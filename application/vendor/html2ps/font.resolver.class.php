<?php
// $Header: /cvsroot/html2ps/font.resolver.class.php,v 1.15 2006/11/11 13:43:52 Konstantin Exp $

require_once(HTML2PS_DIR.'font.constants.inc.php');

class FontResolver {
  var $families = array();
  var $aliases = array();

  var $overrides = array();
  var $overrides_mask = array();

  var $ttf_mappings = array();
  var $afm_mappings = array();

  var $ps_fonts = array();
  var $ps_fonts_counter = 1;

  function FontResolver() {
  }

  function setup_ttf_mappings($pdf) {
    foreach ($this->ttf_mappings as $typeface => $file) {
      pdf_set_parameter($pdf, "FontOutline", $typeface."=".TTF_FONTS_REPOSITORY.$file); 
    };
  }

  function add_ttf_mapping($typeface, $file, $embed) {
    $this->ttf_mappings[$typeface] = $file;
    $this->embed[$typeface]        = $embed;
  }

  function add_afm_mapping($typeface, $file) {
    $this->afm_mappings[$typeface] = $file;
  }

  function font_resolved($family, $weight, $style, $encoding) {
    return 
      isset($this->ps_fonts[$family]) and
      isset($this->ps_fonts[$family][$weight]) and
      isset($this->ps_fonts[$family][$weight][$style]) and
      isset($this->ps_fonts[$family][$weight][$style][$encoding]);
  }

  function get_afm_mapping($typeface) {
    return (isset($this->afm_mappings[$typeface]) ?
            $this->afm_mappings[$typeface] : 
            "");
  }

  function resolve_font($family, $weight, $style, $encoding) {
    if (!$this->font_resolved($family, $weight, $style, $encoding)) {
      $this->ps_fonts[$family][$weight][$style][$encoding] = 'font'.$this->ps_fonts_counter;
      $this->ps_fonts_counter++;
    };
    return $this->ps_fonts[$family][$weight][$style][$encoding];
  }
  
  function add_family_normal_encoding_override($family, $encoding, $normal, $italic, $oblique) {
    $this->overrides[$encoding][$family][WEIGHT_NORMAL][FS_NORMAL_STYLE]  = $normal;
    $this->overrides[$encoding][$family][WEIGHT_NORMAL][FS_ITALIC_STYLE]  = $italic;
    $this->overrides[$encoding][$family][WEIGHT_NORMAL][FS_OBLIQUE] = $oblique;
  }

  function add_family_normal_encoding_override_mask($family, $encoding, $normal, $italic, $oblique) {
    $this->overrides_mask[$family][WEIGHT_NORMAL][FS_NORMAL_STYLE][]  = array('mask' => $encoding,
                                                                        'override' => $normal);
    $this->overrides_mask[$family][WEIGHT_NORMAL][FS_ITALIC_STYLE][]  = array('mask' => $encoding,
                                                                        'override' => $italic);
    $this->overrides_mask[$family][WEIGHT_NORMAL][FS_OBLIQUE][] = array('mask' => $encoding,
                                                                        'override' => $oblique);
  }

  function add_family_bold_encoding_override($family, $encoding, $normal, $italic, $oblique) {
    $this->overrides[$encoding][$family][WEIGHT_BOLD][FS_NORMAL_STYLE]  = $normal;
    $this->overrides[$encoding][$family][WEIGHT_BOLD][FS_ITALIC_STYLE]  = $italic;
    $this->overrides[$encoding][$family][WEIGHT_BOLD][FS_OBLIQUE] = $oblique;
  }

  function add_family_bold_encoding_override_mask($family, $encoding, $normal, $italic, $oblique) {
    $this->overrides_mask[$family][WEIGHT_BOLD][FS_NORMAL_STYLE][]  = array('mask' => $encoding,
                                                                      'override' => $normal);
    $this->overrides_mask[$family][WEIGHT_BOLD][FS_ITALIC_STYLE][]  = array('mask' => $encoding,
                                                                      'override' => $italic);
    $this->overrides_mask[$family][WEIGHT_BOLD][FS_OBLIQUE][] = array('mask' => $encoding,
                                                                      'override' => $oblique);
  }

  function add_normal_encoding_override($encoding, $normal, $italic, $oblique) {
    $this->add_family_normal_encoding_override(" ", $encoding, $normal, $italic, $oblique);
  }

  function add_normal_encoding_override_mask($encoding, $normal, $italic, $oblique) {
    $this->add_family_normal_encoding_override_mask(" ", $encoding, $normal, $italic, $oblique);
  }

  function add_bold_encoding_override($encoding, $normal, $italic, $oblique) {
    $this->add_family_bold_encoding_override(" ", $encoding, $normal, $italic, $oblique);
  }

  function add_bold_encoding_override_mask($encoding, $normal, $italic, $oblique) {
    $this->add_family_bold_encoding_override_mask(" ", $encoding, $normal, $italic, $oblique);
  }

  function get_global_encoding_override($weight, $style, $encoding) {
    return $this->get_family_encoding_override(" ", $weight, $style, $encoding);
  }

  function get_family_encoding_override($family, $weight, $style, $encoding) {
    if (isset($this->overrides[$encoding]) &&
        isset($this->overrides[$encoding][$family]) &&
        isset($this->overrides[$encoding][$family][$weight]) &&
        isset($this->overrides[$encoding][$family][$weight][$style])) {
      return $this->overrides[$encoding][$family][$weight][$style];
    };

    if (isset($this->overrides_mask[$family]) && 
        isset($this->overrides_mask[$family][$weight]) && 
        isset($this->overrides_mask[$family][$weight][$style])) {
      foreach ($this->overrides_mask[$family][$weight][$style] as $override) {
        if (preg_match($override['mask'], $encoding)) {
          return $override['override'];
        };
      };
    };

    return '';
  }

  function have_global_encoding_override($weight, $style, $encoding) {
    return $this->get_global_encoding_override($weight, $style, $encoding) !== "";
  }

  function have_family_encoding_override($family, $weight, $style, $encoding) {
    return $this->get_family_encoding_override($family, $weight, $style, $encoding) !== "";
  }

  function add_alias($alias, $family) { $this->aliases[$alias] = $family; }

  function add_normal_family($family, $normal, $italic, $oblique) {
    $this->families[$family][WEIGHT_NORMAL][FS_NORMAL_STYLE]  = $normal;
    $this->families[$family][WEIGHT_NORMAL][FS_ITALIC_STYLE]  = $italic;
    $this->families[$family][WEIGHT_NORMAL][FS_OBLIQUE] = $oblique;
  }

  function add_bold_family($family, $normal, $italic, $oblique) {
    $this->families[$family][WEIGHT_BOLD][FS_NORMAL_STYLE]  = $normal;
    $this->families[$family][WEIGHT_BOLD][FS_ITALIC_STYLE]  = $italic;
    $this->families[$family][WEIGHT_BOLD][FS_OBLIQUE] = $oblique;
  }

  function get_typeface_name($family, $weight, $style, $encoding) {
    if ($this->have_alias($family)) {
      return $this->get_typeface_name($this->aliases[$family], $weight, $style, $encoding);
    }

    // Check for family-specific encoding override
    if ($this->have_family_encoding_override($family, $weight, $style, $encoding)) {
      return $this->get_family_encoding_override($family, $weight, $style, $encoding);
    }

    // Check for global encoding override
    if ($this->have_global_encoding_override($weight, $style, $encoding)) {
      return $this->get_global_encoding_override($weight, $style, $encoding);
    }

    if (!isset($this->families[$family])) { return "Times-Roman"; };
    if (!isset($this->families[$family][$weight])) { return "Times-Roman"; };
    if (!isset($this->families[$family][$weight][$style])) { return "Times-Roman"; };

    return $this->families[$family][$weight][$style];
  }

  function have_alias($family) { 
    return isset($this->aliases[$family]); 
  }

  function have_font_family($family) { 
    return isset($this->families[$family]) or $this->have_alias($family); 
  }
}

global $g_font_resolver, $g_font_resolver_pdf;
$g_font_resolver = new FontResolver();
$g_font_resolver_pdf = new FontResolver();

?>
