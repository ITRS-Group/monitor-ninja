QUnit.module('LSFilter_Lib');

QUnit.test('Function span_icon returns span-icon element with all parameters', function(assert) {
	var icon = span_icon('icon-up', 'The icon title');
	assert.equal(icon.length, 1);
	assert.equal(icon.attr('title'), 'The icon title');
	assert.equal(icon.attr('class'), 'icon-up');
});

QUnit.test('Function span_icon returns span-icon element without title', function(assert) {
	var icon = span_icon('icon-up');
	assert.equal(icon.length, 1);
	assert.ok(typeof(icon.attr('title')) == 'undefined');
	assert.equal(icon.attr('class'), 'icon-up');
});

QUnit.test('Function span_icon returns span-icon element without class and title', function(assert) {
	var icon = span_icon();
	assert.equal(icon.length, 1);
	assert.ok(typeof(icon.attr('title')) == 'undefined');
	// icon-help is displayed when no icon class is given
	assert.equal(icon.attr('class'), 'icon-help');
});
