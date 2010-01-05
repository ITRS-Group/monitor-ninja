<?php

class TestInputText extends GenericTest {
  function TestInputText1() {
    $tree = $this->runPipeline('
<style type="text/css">
.input1 {
  font-size: 10px;
}

.input2 {
  font-size: 20px;
}
</style>

<input id="i1" class="input1" type="text" size="0"/><br/>
<div style="width: 122px; height: 10px; background: green;">&nbsp;</div>

<input id="i2" class="input1" type="text" size="1"/><br/>
<div style="width: 27px; height: 10px; background: green;">&nbsp;</div>

<input id="i3" class="input1" type="text" size="2"/><br/>
<div style="width: 32px; height: 10px; background: green;">&nbsp;</div>

<input id="i4" class="input1" type="text" size="3"/><br/>
<div style="width: 37px; height: 10px; background: green;">&nbsp;</div>

<input id="i5" class="input1" type="text" size="4"/><br/>
<div style="width: 42px; height: 10px; background: green;">&nbsp;</div>

<input id="i6" class="input1" type="text" size="5"/><br/>
<div style="width: 47px; height: 10px; background: green;">&nbsp;</div>

<input id="i7" class="input1" type="text" size="10"/><br/>
<div style="width: 72px; height: 10px; background: green;">&nbsp;</div>

<input id="i8" class="input1" type="text" size="15"/><br/>
<div style="width: 97px; height: 10px; background: green;">&nbsp;</div>

<input id="i9" class="input1" type="text" size="20"/><br/>
<div style="width: 122px; height: 10px; background: green;">&nbsp;</div>

<input id="i10" class="input1" type="text" size="25"/><br/>
<div style="width: 147px; height: 10px; background: green;">&nbsp;</div>

<input id="i11" class="input2" type="text" size="0"/><br/>
<div style="width: 219px; height: 10px; background: green;">&nbsp;</div>

<input id="i12" class="input2" type="text" size="1"/><br/>
<div style="width: 48px; height: 10px; background: green;">&nbsp;</div>

<input id="i13" class="input2" type="text" size="2"/><br/>
<div style="width: 57px; height: 10px; background: green;">&nbsp;</div>

<input id="i14" class="input2" type="text" size="3"/><br/>
<div style="width: 66px; height: 10px; background: green;">&nbsp;</div>

<input id="i15" class="input2" type="text" size="4"/><br/>
<div style="width: 75px; height: 10px; background: green;">&nbsp;</div>

<input id="i16" class="input2" type="text" size="5"/><br/>
<div style="width: 84px; height: 10px; background: green;">&nbsp;</div>

<input id="i17" class="input2" type="text" size="10"/><br/>
<div style="width: 129px; height: 10px; background: green;">&nbsp;</div>

<input id="i18" class="input2" type="text" size="15"/><br/>
<div style="width: 174px; height: 10px; background: green;">&nbsp;</div>

<input id="i19" class="input2" type="text" size="20"/><br/>
<div style="width: 219px; height: 10px; background: green;">&nbsp;</div>

<input id="i20" class="input2" type="text" size="25"/><br/>
<div style="width: 264px; height: 10px; background: green;">&nbsp;</div>
');

    $widths = array(122,
                    27,
                    32,
                    37,
                    42,
                    47,
                    72,
                    97,
                    122,
                    147,
                    219,
                    48,
                    57,
                    66,
                    75,
                    84,
                    129,
                    174,
                    219,
                    264);

    for ($i=0; $i<20; $i++) {
      $element =& $tree->get_element_by_id(sprintf('i%d', $i+1));
      $this->assertEqual($element->get_full_width(), 
                         px2pt($widths[$i]),
                         sprintf('Invalid input No %i width [%%s]', $i+1));
    };
  }
}

?>