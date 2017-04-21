QUnit.module("Urlparams");

QUnit.test("test getUrlParam() valid input", function(assert) {
	assert.ok('1' == getUrlParam('standardreport', 'http://localhost/monitor/index.php/summary/edit_settings?standardreport=1'), "Expected output: 1.");
	assert.ok('1' == getUrlParam('blaha','edit_settings?blaha=1&buhu=0'), "Expected output: 1.");
	assert.ok('[1]' == getUrlParam('blaha','edit_settings?blaha=[1]'), "Expected output: [1]");
	assert.ok('abcd' == getUrlParam('blaha','edit_settings?blaha=abcd&gjk=efgh'), "Expected output: abcd");
});

QUnit.test("test getUrlParam() invalid input", function(assert) {
	assert.ok('' == getUrlParam('customreport','localhost/monitor/index.php/summary/edit_settings?standardreport'), "Expected empty string as output.");
	assert.ok('' == getUrlParam('blaha','edit_settings?blaha=&buhu=0'), "Expected empty string as output.");
	assert.ok('' == getUrlParam('blaha','edit_settings?bl#_ha=1'), "Expected empty string as output.");
	assert.ok('' == getUrlParam('blaha','edit_settings?blaha[]=1'), "Expected empty string as output.");
});
