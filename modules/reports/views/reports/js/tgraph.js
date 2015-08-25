/**
*	TGraph - JavaScript graphing utility
*/

TGraphEventBinder = function (node, event, callback) {
	if (node.addEventListener) {
		node.addEventListener(event, callback, false);
	} else {
		node.attachEvent('on' + event, callback);
	}
}

var TGraph = function (stops, type, name, max, upscale) {

		/**
		* @param object stops
		*	@param string type = bar
		* @param number width = 500
		* @param number max = 400
		*/

		var w = document.getElementById('tgraph').clientWidth;

		this.upscale = upscale || false;
		this.name = name || 'Graph';
		this.stops = stops;
		this.type = type || 'bar';
		this.width = w * 0.8;
		this.max = max || 400;
		this.blocks = [];

		this.hoverbox = document.createElement('div');
		this.container = document.createElement('div');

		this.container.className = 'tgraph-container';
		this.hoverbox.className = 'tgraph-hoverbox';
		this.label = document.createElement('label');

		this.label.innerHTML = name;
		this.label.className = 'tgraph-label';

		this.container.style.overflow = 'auto';
		this.container.style.width = '100%';
		this.create();
		this.addHover();

		document.body.appendChild(this.hoverbox);

		var tgraph_el = document.getElementById('tgraph');
		while( tgraph_el.firstChild ) { /* Remove all children */
			tgraph_el.removeChild(tgraph_el.firstChild);
		}
		tgraph_el.appendChild(this.container);

};

TGraph.prototype = {

	addHover: function () {

		var that = this;


		TGraphEventBinder(this.container, 'mousemove', function (e) {

			var posx = 0, posy = 0;

			if (e.pageX || e.pageY) {
				posy = e.pageY;
				posx = e.pageX;
			} else {
				posy = document.body.scrollTop + e.clientY;
				posx = document.body.scrollLeft + e.clientX;
			}

			if (posx - that.container.offsetParent.offsetLeft > that.container.offsetWidth / 2) {
				posx -= that.hoverbox.offsetWidth;
				posx -= 10;
			} else {
				posx += 15;
			}

			posy += 15;

			that.hoverbox.style.left = posx + 'px';
			that.hoverbox.style.top = posy + 'px';

		})


		TGraphEventBinder(this.container, 'mouseover', function (e) {

			e = e || window.event;

			if (!e.target) {
				e.target = e.srcElement;
			}

			if (e.target.value) {
				that.hoverbox.style.display = 'block';
				that.hoverbox.innerHTML = e.target.value;
			}

		});

		TGraphEventBinder(this.container, 'mouseout', function (e) {

			e = e || window.event;

			that.hoverbox.style.display = 'none';
			that.hoverbox.innerHTML = '';
		});

	},

	hoverText: function (stop, time) {
		return '<b class="title">' + stop['label'] +'</b><br />'+
			'<small>' + this.parseNiceTime(new Date(time)) + ' - ' +
			this.parseNiceTime(new Date(time + stop.duration)) +
			'</small>' +
			((stop['short']) ? '<br />' + stop['short'] : '');
	},

	formatNumber: function (n) {
		if (n < 10) {
			return '0' + n;
		} else {
			return n;
		}
	},

	parseNiceTime: function (date) {
		return date.getFullYear() +'-'+
			this.formatNumber((date.getMonth() + 1)) + '-' +
			this.formatNumber(date.getDate()) + ' ' +
			this.formatNumber(date.getHours()) + ':' +
			this.formatNumber(date.getMinutes());
	},

	parseNiceTimeHigh: function (date) {
		return date.getFullYear() +'-'+
			this.formatNumber((date.getMonth() + 1)) + '-' +
			this.formatNumber(date.getDate());
	},

	parseNiceTimeSuperHigh: function (date) {
		return this.formatNumber((date.getMonth() + 1)) + '-' +
			this.formatNumber(date.getDate());
	},

	createBlock: function (stop, starttime) {
		var block = document.createElement('div');

		block.style.left = ((starttime / this.max) * 100) + '%';
		block.style.right = (100 - ((starttime + stop.duration) / this.max) * 100) + '%';
		block.style.background = stop.color;
		block.className = 'tgraph-block';

		return block;
	},

	addNote: function (stop, skew) {
		note = document.createElement('div');
		note.className = 'tgraph-note';
		note.style.background = stop['color'];
		note.style.marginTop = (parseInt(note.style.marginTop || '-6', 10) - skew) + 'px';
		note.style.marginLeft = (parseInt(note.style.marginLeft || '-4', 10) + skew) + 'px';
		stop.block.appendChild(note);
	},

	drawScale: function () {

		var line = null,
			graph = document.createElement('div'),
			el = null,
			clear = document.createElement('div'),
			shigh = ((new Date(this.start)).getFullYear() == (new Date(this.start + this.max)).getFullYear());

		clear.style.clear = 'both';
		graph.className = 'tgraph';
		//graph.style.width = this.width + 'px';
		graph.style.height = '40px';
		graph.style.border = 'none';

		clone = this.label.cloneNode(true);
		clone.innerHTML = "";

		for (var i = 0; i <= 6; i += 1) {

			line = document.createElement('div');
			line.className = 'tgraph-time-line';
			line.style.height = (this.height) + 'px';
			line.style.marginTop = '-' + (this.height + 0) + 'px';

			el = document.createElement('div');
			el.style.width = ((1 / 7) * 100) + '%';
			el.className = 'tgraph-time';

			if (shigh) {
				el.innerHTML = '&nbsp;&nbsp;' + this.parseNiceTimeSuperHigh(new Date(this.start + ((this.max / 7) * i)));
			} else {
				el.innerHTML = '&nbsp;&nbsp;' + this.parseNiceTimeHigh(new Date(this.start + ((this.max / 7) * i)));
			}

			el.appendChild(line);

			graph.appendChild(el);
		}

		this.container.appendChild(clear);
		this.container.appendChild(clone);
		this.container.appendChild(graph);

	},

	/**
	 * The subline's sub-blocks all currently have coordinates relative to the line, they must be translated
	 * into coordinates relativeto the subline.
	 * The subline itself should have a width of (length of interruption * 2) and be neatly centered.
	 */
	rescaleSubline: function (subline) {
		var scale_from = parseFloat(subline.style.left.slice(0,-1));
		var scale_to = 100 - parseFloat(subline.style.right.slice(0,-1));
		for (var si = 0; si < subline.children.length; si++) {
			var child = subline.children[si];
			var orig_from = parseFloat(child.style.left.slice(0,-1));
			var orig_to = 100 - parseFloat(child.style.right.slice(0,-1));
			child.style.left = (((orig_from - scale_from) / (orig_to - scale_from)) * 100) + '%';
			child.style.right = (100 - ((orig_to - scale_from) / (orig_to - scale_from)) * 100) + '%';
		}
		subline.style.left = Math.max(0, scale_from - (scale_to - scale_from) / 2) + '%';
		subline.style.right = 100 - Math.min(100, scale_to + (scale_to - scale_from) / 2) + '%';
	},

	create: function () {

		var time = null,
			note = null,
			line = null,
			graph = null,
			lclone = null,
			subline = null,
			clear = document.createElement('div'),
			skew = 0,
			i = 0,
			running_duration = 0;

		clear.style.clear = 'both';
		this.start = this.max * 1000;
		this.height = 0;

		this.max = 0;
		if (this.stops.length > 0) {
			for (i; i < this.stops[0].length; i += 1) {
				this.max += this.stops[0][i].duration * 1000;
			}
		}

		var lastHost = '';

		for (var y = 0; y < this.stops.length; y += 1) {

			graph = document.createElement('div');
			line = document.createElement('div');
			subline = document.createElement('div');
			i = 0;

			graph.className = 'tgraph';
			subline.className = 'tgraph-subline';
			line.className = 'tgraph-block-line';

			var cHost = this.name[y].split(';')[0],
				sName = this.name[y].split(';')[1];

			lclone = this.label.cloneNode(true);

			if (cHost === lastHost) {
				lclone.innerHTML = sName;
			} else {
				lclone.innerHTML = '<strong>' + cHost + '</strong>' + ((sName) ? ' ; ' + sName: '');
			}

			lastHost = cHost;

			time = this.start;
			running_duration = 0;
			laststate = '';

			this.height += 40;

			for (i; this.stops[y] && i < this.stops[y].length; i += 1) {

				this.stops[y][i].duration = this.stops[y][i].duration * 1000;
				this.stops[y][i].index = i;

				this.stops[y][i].block = this.createBlock(this.stops[y][i], running_duration);

				if (this.upscale === true && (this.stops[y][i].duration / this.max) < 0.03 && this.stops[y][i].color != 'transparent') {
					clone = this.stops[y][i].block.cloneNode(true);

					this.stops[y][i].block.style.background = "#333";

					clone.value = this.hoverText(this.stops[y][i], time);
					subline.style.right = this.stops[y][i].block.style.right;
					if (subline.children.length == 0)
						subline.style.left = this.stops[y][i].block.style.left;
					subline.appendChild(clone);
				} else {
					if (subline.children.length > 0) {
						this.rescaleSubline(subline)
						line.appendChild(subline);
						subline = document.createElement('div');
						subline.className = 'tgraph-subline';
					}

					this.stops[y][i].block.value = this.hoverText(this.stops[y][i], time);
				}

				line.appendChild(this.stops[y][i].block);

				time += this.stops[y][i].duration;
				running_duration += this.stops[y][i].duration;

			}
					if (subline.children.length > 0) {
						this.rescaleSubline(subline)
						line.appendChild(subline);
					}

			if (!document.addEventListener) {
				var clear = document.createElement('div');
				clear.style.clear = 'both';
				line.appendChild(clear);
			}

			graph.appendChild(line);
			this.container.appendChild(lclone);
			this.container.appendChild(graph);
			this.container.appendChild(clear.cloneNode(true));

		}

		this.drawScale();

	}

};
