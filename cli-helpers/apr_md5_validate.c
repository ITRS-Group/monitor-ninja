/*
 * This is work is derived from material Copyright RSA Data Security, Inc.
 *
 * The RSA copyright statement and Licence for that original material is
 * included below. This is followed by the Apache copyright statement and
 * licence for the modifications made to that material.
 */

/* Copyright (C) 1991-2, RSA Data Security, Inc. Created 1991. All
   rights reserved.

   License to copy and use this software is granted provided that it
   is identified as the "RSA Data Security, Inc. MD5 Message-Digest
   Algorithm" in all material mentioning or referencing this software
   or this function.

   License is also granted to make and use derivative works provided
   that such works are identified as "derived from the RSA Data
   Security, Inc. MD5 Message-Digest Algorithm" in all material
   mentioning or referencing the derived work.

   RSA Data Security, Inc. makes no representations concerning either
   the merchantability of this software or the suitability of this
   software for any particular purpose. It is provided "as is"
   without express or implied warranty of any kind.

   These notices must be retained in any copies of any part of this
   documentation and/or software.
 */

/* Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * Nearly all code in this file was taken from the apr library as found
 * in the Apache Software Foundation's subversion repository for the 'apr'
 * utility library. The code has been restructured to isolate the
 * apr_md5_encode() function, which creates salted md5 hashes of
 * text-strings. The hashes thus created are suitable to use for
 * password storage.
 *
 * The idea is that php applications that previously relied on basic-auth
 * can convert to using its own authentication scheme without forcing
 * users to re-create their passwords.
 *
 * Do note that this application is not very secure, as it takes both
 * the plaintext and the hashed password as command-line arguments. It
 * can most certainly be improved, but it suffices for our simple needs
 *
 * It's safe to assume that any and all bugs in the code was introduced
 * by me.
 *
 * /Andreas Ericsson <ae@op5.com>
 */

#include <sys/types.h>
#include <limits.h>
#include <stdint.h>

#include <stdio.h>
#include <string.h>
#include <crypt.h>
#include <unistd.h>

/* The MD5 digest size */
#define APR_MD5_DIGESTSIZE 16

/* MD5 context. */
struct apr_md5_ctx_t {
    uint32_t state[4];         /* state (ABCD) */
    uint32_t count[2];         /* number of bits, modulo 2^64 (lsb first) */
    unsigned char buffer[64];  /* input buffer */
};

typedef struct apr_md5_ctx_t apr_md5_ctx_t;

/* Constants for MD5Transform routine. */
#define S11 7
#define S12 12
#define S13 17
#define S14 22
#define S21 5
#define S22 9
#define S23 14
#define S24 20
#define S31 4
#define S32 11
#define S33 16
#define S34 23
#define S41 6
#define S42 10
#define S43 15
#define S44 21

static const unsigned char PADDING[64] =
{
	0x80, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
		0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
		0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
};

#define DO_XLATE 0
#define SKIP_XLATE 1

/* F, G, H and I are basic MD5 functions. */
#define F(x, y, z) (((x) & (y)) | ((~x) & (z)))
#define G(x, y, z) (((x) & (z)) | ((y) & (~z)))
#define H(x, y, z) ((x) ^ (y) ^ (z))
#define I(x, y, z) ((y) ^ ((x) | (~z)))

/* ROTATE_LEFT rotates x left n bits. */
#define ROTATE_LEFT(x, n) (((x) << (n)) | ((x) >> (32-(n))))

/* FF, GG, HH, and II transformations for rounds 1, 2, 3, and 4.
 * Rotation is separate from addition to prevent recomputation.
 */
#define FF(a, b, c, d, x, s, ac) { \
 (a) += F ((b), (c), (d)) + (x) + (uint32_t)(ac); \
 (a) = ROTATE_LEFT ((a), (s)); \
 (a) += (b); \
  }
#define GG(a, b, c, d, x, s, ac) { \
 (a) += G ((b), (c), (d)) + (x) + (uint32_t)(ac); \
 (a) = ROTATE_LEFT ((a), (s)); \
 (a) += (b); \
  }
#define HH(a, b, c, d, x, s, ac) { \
 (a) += H ((b), (c), (d)) + (x) + (uint32_t)(ac); \
 (a) = ROTATE_LEFT ((a), (s)); \
 (a) += (b); \
  }
#define II(a, b, c, d, x, s, ac) { \
 (a) += I ((b), (c), (d)) + (x) + (uint32_t)(ac); \
 (a) = ROTATE_LEFT ((a), (s)); \
 (a) += (b); \
  }


/* Encodes input (uint32_t) into output (unsigned char). Assumes len is
 * a multiple of 4. */
static void Encode(unsigned char *output, const uint32_t *input,
				   unsigned int len)
{
	unsigned int i, j;
	uint32_t k;

	for (i = 0, j = 0; j < len; i++, j += 4) {
		k = input[i];
		output[j]	 = (unsigned char)(k & 0xff);
		output[j + 1] = (unsigned char)((k >> 8) & 0xff);
		output[j + 2] = (unsigned char)((k >> 16) & 0xff);
		output[j + 3] = (unsigned char)((k >> 24) & 0xff);
	}
}

/* Decodes input (unsigned char) into output (uint32_t). Assumes len is
 * a multiple of 4. */
static void Decode(uint32_t *output, const unsigned char *input,
				   unsigned int len)
{
	unsigned int i, j;

	for (i = 0, j = 0; j < len; i++, j += 4)
		output[i] = ((uint32_t)input[j])			 |
		(((uint32_t)input[j + 1]) << 8)  |
		(((uint32_t)input[j + 2]) << 16) |
		(((uint32_t)input[j + 3]) << 24);
}


/* MD5 basic transformation. Transforms state based on block. */
static void MD5Transform(uint32_t state[4], const unsigned char block[64])
{
	uint32_t a = state[0], b = state[1], c = state[2], d = state[3],
		x[APR_MD5_DIGESTSIZE];

	Decode(x, block, 64);

	/* Round 1 */
	FF(a, b, c, d, x[0],  S11, 0xd76aa478); /* 1 */
	FF(d, a, b, c, x[1],  S12, 0xe8c7b756); /* 2 */
	FF(c, d, a, b, x[2],  S13, 0x242070db); /* 3 */
	FF(b, c, d, a, x[3],  S14, 0xc1bdceee); /* 4 */
	FF(a, b, c, d, x[4],  S11, 0xf57c0faf); /* 5 */
	FF(d, a, b, c, x[5],  S12, 0x4787c62a); /* 6 */
	FF(c, d, a, b, x[6],  S13, 0xa8304613); /* 7 */
	FF(b, c, d, a, x[7],  S14, 0xfd469501); /* 8 */
	FF(a, b, c, d, x[8],  S11, 0x698098d8); /* 9 */
	FF(d, a, b, c, x[9],  S12, 0x8b44f7af); /* 10 */
	FF(c, d, a, b, x[10], S13, 0xffff5bb1); /* 11 */
	FF(b, c, d, a, x[11], S14, 0x895cd7be); /* 12 */
	FF(a, b, c, d, x[12], S11, 0x6b901122); /* 13 */
	FF(d, a, b, c, x[13], S12, 0xfd987193); /* 14 */
	FF(c, d, a, b, x[14], S13, 0xa679438e); /* 15 */
	FF(b, c, d, a, x[15], S14, 0x49b40821); /* 16 */

	/* Round 2 */
	GG(a, b, c, d, x[1],  S21, 0xf61e2562); /* 17 */
	GG(d, a, b, c, x[6],  S22, 0xc040b340); /* 18 */
	GG(c, d, a, b, x[11], S23, 0x265e5a51); /* 19 */
	GG(b, c, d, a, x[0],  S24, 0xe9b6c7aa); /* 20 */
	GG(a, b, c, d, x[5],  S21, 0xd62f105d); /* 21 */
	GG(d, a, b, c, x[10], S22, 0x2441453);  /* 22 */
	GG(c, d, a, b, x[15], S23, 0xd8a1e681); /* 23 */
	GG(b, c, d, a, x[4],  S24, 0xe7d3fbc8); /* 24 */
	GG(a, b, c, d, x[9],  S21, 0x21e1cde6); /* 25 */
	GG(d, a, b, c, x[14], S22, 0xc33707d6); /* 26 */
	GG(c, d, a, b, x[3],  S23, 0xf4d50d87); /* 27 */
	GG(b, c, d, a, x[8],  S24, 0x455a14ed); /* 28 */
	GG(a, b, c, d, x[13], S21, 0xa9e3e905); /* 29 */
	GG(d, a, b, c, x[2],  S22, 0xfcefa3f8); /* 30 */
	GG(c, d, a, b, x[7],  S23, 0x676f02d9); /* 31 */
	GG(b, c, d, a, x[12], S24, 0x8d2a4c8a); /* 32 */

	/* Round 3 */
	HH(a, b, c, d, x[5],  S31, 0xfffa3942); /* 33 */
	HH(d, a, b, c, x[8],  S32, 0x8771f681); /* 34 */
	HH(c, d, a, b, x[11], S33, 0x6d9d6122); /* 35 */
	HH(b, c, d, a, x[14], S34, 0xfde5380c); /* 36 */
	HH(a, b, c, d, x[1],  S31, 0xa4beea44); /* 37 */
	HH(d, a, b, c, x[4],  S32, 0x4bdecfa9); /* 38 */
	HH(c, d, a, b, x[7],  S33, 0xf6bb4b60); /* 39 */
	HH(b, c, d, a, x[10], S34, 0xbebfbc70); /* 40 */
	HH(a, b, c, d, x[13], S31, 0x289b7ec6); /* 41 */
	HH(d, a, b, c, x[0],  S32, 0xeaa127fa); /* 42 */
	HH(c, d, a, b, x[3],  S33, 0xd4ef3085); /* 43 */
	HH(b, c, d, a, x[6],  S34, 0x4881d05);  /* 44 */
	HH(a, b, c, d, x[9],  S31, 0xd9d4d039); /* 45 */
	HH(d, a, b, c, x[12], S32, 0xe6db99e5); /* 46 */
	HH(c, d, a, b, x[15], S33, 0x1fa27cf8); /* 47 */
	HH(b, c, d, a, x[2],  S34, 0xc4ac5665); /* 48 */

	/* Round 4 */
	II(a, b, c, d, x[0],  S41, 0xf4292244); /* 49 */
	II(d, a, b, c, x[7],  S42, 0x432aff97); /* 50 */
	II(c, d, a, b, x[14], S43, 0xab9423a7); /* 51 */
	II(b, c, d, a, x[5],  S44, 0xfc93a039); /* 52 */
	II(a, b, c, d, x[12], S41, 0x655b59c3); /* 53 */
	II(d, a, b, c, x[3],  S42, 0x8f0ccc92); /* 54 */
	II(c, d, a, b, x[10], S43, 0xffeff47d); /* 55 */
	II(b, c, d, a, x[1],  S44, 0x85845dd1); /* 56 */
	II(a, b, c, d, x[8],  S41, 0x6fa87e4f); /* 57 */
	II(d, a, b, c, x[15], S42, 0xfe2ce6e0); /* 58 */
	II(c, d, a, b, x[6],  S43, 0xa3014314); /* 59 */
	II(b, c, d, a, x[13], S44, 0x4e0811a1); /* 60 */
	II(a, b, c, d, x[4],  S41, 0xf7537e82); /* 61 */
	II(d, a, b, c, x[11], S42, 0xbd3af235); /* 62 */
	II(c, d, a, b, x[2],  S43, 0x2ad7d2bb); /* 63 */
	II(b, c, d, a, x[9],  S44, 0xeb86d391); /* 64 */

	state[0] += a;
	state[1] += b;
	state[2] += c;
	state[3] += d;

	/* Zeroize sensitive information. */
	memset(x, 0, sizeof(x));
}

/* MD5 initialization. Begins an MD5 operation, writing a new context.
 */
static int apr_md5_init(apr_md5_ctx_t *context)
{
	context->count[0] = context->count[1] = 0;

	/* Load magic initialization constants. */
	context->state[0] = 0x67452301;
	context->state[1] = 0xefcdab89;
	context->state[2] = 0x98badcfe;
	context->state[3] = 0x10325476;

	return 0;
}

/* MD5 block update operation. Continues an MD5 message-digest
 * operation, processing another message block, and updating the
 * context. */
static int md5_update_buffer(apr_md5_ctx_t *context,
							 const void *vinput,
							 size_t inputLen,
							 int xlate_buffer)
{
	const unsigned char *input = vinput;
	unsigned int i, idx, partLen;

	/* Compute number of bytes mod 64 */
	idx = (unsigned int)((context->count[0] >> 3) & 0x3F);

	/* Update number of bits */
	if ((context->count[0] += ((uint32_t)inputLen << 3))
		< ((uint32_t)inputLen << 3))
		context->count[1]++;
	context->count[1] += (uint32_t)inputLen >> 29;

	partLen = 64 - idx;

	/* Transform as many times as possible. */
	if (inputLen >= partLen) {
		memcpy(&context->buffer[idx], input, partLen);
		MD5Transform(context->state, context->buffer);

		for (i = partLen; i + 63 < inputLen; i += 64)
			MD5Transform(context->state, &input[i]);

		idx = 0;
	}
	else
		i = 0;

	/* Buffer remaining input */
	memcpy(&context->buffer[idx], &input[i], inputLen - i);
	return 0;
}

/* MD5 block update operation. API with the default setting
 * for EBCDIC translations */
static int apr_md5_update(apr_md5_ctx_t *context, const void *input, size_t inputLen)
{
	return md5_update_buffer(context, input, inputLen, DO_XLATE);
}

/* MD5 finalization. Ends an MD5 message-digest operation, writing the
 * the message digest and zeroizing the context */
static int apr_md5_final(unsigned char digest[APR_MD5_DIGESTSIZE],
						 apr_md5_ctx_t *context)
{
	unsigned char bits[8];
	unsigned int idx, padLen;

	/* Save number of bits */
	Encode(bits, context->count, 8);

	/* Pad out to 56 mod 64. */
	idx = (unsigned int)((context->count[0] >> 3) & 0x3f);
	padLen = (idx < 56) ? (56 - idx) : (120 - idx);
	apr_md5_update(context, PADDING, padLen);

	/* Append length (before padding) */
	apr_md5_update(context, bits, 8);

	/* Store state in digest */
	Encode(digest, context->state, APR_MD5_DIGESTSIZE);

	/* Zeroize sensitive information. */
	memset(context, 0, sizeof(*context));

	return 0;
}

/*
 * Define the Magic String prefix that identifies a password as being
 * hashed using our algorithm.
 */
static const char *apr1_id = "$apr1$";

/*
 * The following MD5 password encryption code was largely borrowed from
 * the FreeBSD 3.0 /usr/src/lib/libcrypt/crypt.c file, which is
 * licenced as stated at the top of this file.
 */

static void to64(char *s, unsigned long v, int n)
{
	static unsigned char itoa64[] =		 /* 0 ... 63 => ASCII - 64 */
		"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

	while (--n >= 0) {
		*s++ = itoa64[v&0x3f];
		v >>= 6;
	}
}

static int apr_md5_encode(const char *pw, const char *salt,
						  char *result, size_t nbytes)
{
	/*
	 * Minimum size is 8 bytes for salt, plus 1 for the trailing NUL,
	 * plus 4 for the '$' separators, plus the password hash itself.
	 * Let's leave a goodly amount of leeway.
	 */

	char passwd[120], *p;
	const char *sp, *ep;
	unsigned char final[APR_MD5_DIGESTSIZE];
	ssize_t sl, pl, i;
	apr_md5_ctx_t ctx, ctx1;
	unsigned long l;

	/*
	 * Refine the salt first.  It's possible we were given an already-hashed
	 * string as the salt argument, so extract the actual salt value from it
	 * if so.  Otherwise just use the string up to the first '$' as the salt.
	 */
	sp = salt;

	/*
	 * If it starts with the magic string, then skip that.
	 */
	if (!strncmp(sp, apr1_id, strlen(apr1_id))) {
		sp += strlen(apr1_id);
	}

	/*
	 * It stops at the first '$' or 8 chars, whichever comes first
	 */
	for (ep = sp; (*ep != '\0') && (*ep != '$') && (ep < (sp + 8)); ep++) {
		continue;
	}

	/*
	 * Get the length of the true salt
	 */
	sl = ep - sp;

	/*
	 * 'Time to make the doughnuts..'
	 */
	apr_md5_init(&ctx);

	/*
	 * The password first, since that is what is most unknown
	 */
	apr_md5_update(&ctx, pw, strlen(pw));

	/*
	 * Then our magic string
	 */
	apr_md5_update(&ctx, apr1_id, strlen(apr1_id));

	/*
	 * Then the raw salt
	 */
	apr_md5_update(&ctx, sp, sl);

	/*
	 * Then just as many characters of the MD5(pw, salt, pw)
	 */
	apr_md5_init(&ctx1);
	apr_md5_update(&ctx1, pw, strlen(pw));
	apr_md5_update(&ctx1, sp, sl);
	apr_md5_update(&ctx1, pw, strlen(pw));
	apr_md5_final(final, &ctx1);
	for (pl = strlen(pw); pl > 0; pl -= APR_MD5_DIGESTSIZE) {
		md5_update_buffer(&ctx, final,
						  (pl > APR_MD5_DIGESTSIZE) ? APR_MD5_DIGESTSIZE : pl, SKIP_XLATE);
	}

	/*
	 * Don't leave anything around in vm they could use.
	 */
	memset(final, 0, sizeof(final));

	/*
	 * Then something really weird...
	 */
	for (i = strlen(pw); i != 0; i >>= 1) {
		if (i & 1) {
			md5_update_buffer(&ctx, final, 1, SKIP_XLATE);
		}
		else {
			apr_md5_update(&ctx, pw, 1);
		}
	}

	/*
	 * Now make the output string.  We know our limitations, so we
	 * can use the string routines without bounds checking.
	 */
	strcpy(passwd, apr1_id);
	strncat(passwd, sp, sl);
	strcat(passwd, "$");

	apr_md5_final(final, &ctx);

	/*
	 * And now, just to make sure things don't run too fast..
	 * On a 60 Mhz Pentium this takes 34 msec, so you would
	 * need 30 seconds to build a 1000 entry dictionary...
	 */
	for (i = 0; i < 1000; i++) {
		apr_md5_init(&ctx1);
		 /*
		  * apr_md5_final clears out ctx1.xlate at the end of each loop,
		  * so need to to set it each time through
		  */
		if (i & 1) {
			apr_md5_update(&ctx1, pw, strlen(pw));
		}
		else {
			md5_update_buffer(&ctx1, final, APR_MD5_DIGESTSIZE, SKIP_XLATE);
		}
		if (i % 3) {
			apr_md5_update(&ctx1, sp, sl);
		}

		if (i % 7) {
			apr_md5_update(&ctx1, pw, strlen(pw));
		}

		if (i & 1) {
			md5_update_buffer(&ctx1, final, APR_MD5_DIGESTSIZE, SKIP_XLATE);
		}
		else {
			apr_md5_update(&ctx1, pw, strlen(pw));
		}
		apr_md5_final(final,&ctx1);
	}

	p = passwd + strlen(passwd);

	l = (final[ 0]<<16) | (final[ 6]<<8) | final[12]; to64(p, l, 4); p += 4;
	l = (final[ 1]<<16) | (final[ 7]<<8) | final[13]; to64(p, l, 4); p += 4;
	l = (final[ 2]<<16) | (final[ 8]<<8) | final[14]; to64(p, l, 4); p += 4;
	l = (final[ 3]<<16) | (final[ 9]<<8) | final[15]; to64(p, l, 4); p += 4;
	l = (final[ 4]<<16) | (final[10]<<8) | final[ 5]; to64(p, l, 4); p += 4;
	l =					final[11]				; to64(p, l, 2); p += 2;
	*p = '\0';

	/*
	 * Don't leave anything around in vm they could use.
	 */
	memset(final, 0, sizeof(final));

	strncpy(result, passwd, nbytes - 1);
	return 0;
}

#define prefixcmp(a, b) strncmp(a, b, strlen(b))
int main(int argc, char **argv)
{
	char sample[120];
	char *plain, *hash;

	if (argc != 3) {
		printf("Usage: %s <plaintext password> <hashed password>\n", argv[0]);
		return 1;
	}

	plain = argv[1];
	hash = argv[2];

	if (prefixcmp(hash, apr1_id)) {
		printf("Password is not encrypted using apache's md5 hash algorithm\n");
		return 1;
	}

	if (apr_md5_encode(plain, hash, sample, sizeof(sample)) < 0) {
		printf("md5 encoding failed");
		return 1;
	}

	if (!strcmp(hash, sample)) {
		printf("password matches\n");
		return 0;
	}

	printf("password mismatch\n");
	return 1;
}
