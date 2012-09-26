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

var TGraph = function (stops, type, name, width, max) {
		
		/**
		* @param object stops
		*	@param string type = bar
		* @param number width = 500
		* @param number max = 400
		*/
		
		this.name = name || 'Graph';
		this.stops = stops;
		this.type = type || 'bar';
		this.width = width || 500;
		this.max = max || 400;
		this.blocks = [];
		
		this.graph = document.createElement('div');
		this.hoverbox = document.createElement('div');
		this.container = document.createElement('div');
		
		this.graph.className = 'tgraph';
		this.container.className = 'tgraph-container';
		this.hoverbox.className = 'tgraph-hoverbox';
		this.label = document.createElement('label');
		
		this.label.innerHTML = name;
		
		this.label.style.cssFloat = 'left';
		this.label.style.styleFloat = 'left';
		this.label.style.width = '75px';
		this.label.style.paddingTop = '10px';
		this.label.style.textAlign = 'right';
		this.label.style.paddingRight = '10px';
		
		this.container.style.overflow = 'auto';
		this.container.appendChild(this.label);
		this.create();
		this.hookMouseMove();
		
		document.body.appendChild(this.hoverbox);
		
		this.container.appendChild(this.graph);
		return this.container;
	
};

TGraph.prototype = {
	
	addHover: function (stop, block, time) {
	
		var that = this;
	
		TGraphEventBinder(block, 'mouseover', function () {
			that.hoverbox.style.display = 'block';
			
			that.hoverbox.innerHTML = '<b class="title">' + stop['label'] +'</b><br />'+ 
				'<small>' + that.parseNiceTime(new Date(time)) + ' - ' + 
				that.parseNiceTime(new Date(time + stop.duration)) + 
				'</small>' +
				((stop['short']) ? '<br />' + stop['short'] : '');
				
		});
		
		TGraphEventBinder(block, 'mouseout', function () {
			that.hoverbox.style.display = 'none';
			that.hoverbox.innerHTML = '';
		});
		
	},
	
	hookMouseMove: function () {
	
		var that = this;
	
		TGraphEventBinder(window, 'mousemove', function (e) {
			var posx = 0;
			var posy = 0;
			if (!e) var e = window.event;
			if (e.pageX || e.pageY) 	{
				posx = e.pageX;
				posy = e.pageY;
			}
			else if (e.clientX || e.clientY) 	{
				posx = e.clientX + document.body.scrollLeft
					+ document.documentElement.scrollLeft;
				posy = e.clientY + document.body.scrollTop
					+ document.documentElement.scrollTop;
			}
			
			that.hoverbox.style.left = (posx + 15) + 'px';
			that.hoverbox.style.top = (posy + 15) + 'px';
		});
		
	},
	
	create: function () {
		
		this.graph.style.width = this.width + 'px';
		this.container.style.width = (this.width + 100) + 'px';
		
		this.createTimeline();
		
	},
	
	phpDateToTime: function (date) {
		date = date.split(/[- :]/)
		return new Date(date[0], date[1] -1 , date[2], date[3], date[4], date[5]);
	},
	
	formatNumber: function (n) {
		if (n < 10) {
			return '0' + n;
		} else {
			return n;
		}
	},
	
	parseNiceTime: function (date) {
		return this.formatNumber(date.getHours()) + ':' + 
			this.formatNumber(date.getMinutes()) + ' ' + 
			date.getFullYear() +'/'+ 
			this.formatNumber((date.getMonth() + 1)) + '/' + 
			this.formatNumber(date.getDate());
	},
	
	createBlock: function (stop) {
		var block = document.createElement('div');
		
		block.style.width = ((stop.duration / this.max) * 100) + '%';
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
	
	createTimeline: function () {
	
		var time = null,
			note = null,
			line = document.createElement('div'),
			subline = document.createElement('div'),
			skew = 0,
			i = 0;
		
		subline.className = 'tgraph-subline';
		line.className = 'tgraph-block-line';
		
		this.graph.style.height = '40px';
		this.start = this.phpDateToTime(this.max).getTime();
		this.max = 0;
		time = this.start;
		
		for (i; i < this.stops.length; i += 1) {
			this.stops[i].duration = this.stops[i].duration * 1000;
			this.max += this.stops[i].duration;
		}
		
		i = 0;
		
		laststate = '';
		
		for (i; i < this.stops.length; i += 1) {
			
			this.stops[i].index = i;
			this.stops[i].block = this.createBlock(this.stops[i]);
			
			if (((this.stops[i].duration / this.max) * parseInt(this.graph.style.width, 10)) < 5 &&
				laststate != this.stops[i]['label']) {
				
				
				clone = this.stops[i].block.cloneNode(true);
				swidth = (parseFloat(this.stops[i].block.style.width) * 100);
				if (swidth < 2) swidth = 2;
				
				clone.style.width =  swidth + '%';
				subline.appendChild(clone);
				
				this.stops[i].block.style.background = '#fff';
				this.stops[i].block.style.outline = '1px dotted #fff';
				
				//skew += 3;
				//this.addNote(this.stops[i], skew);
				/*
				if (skew > 9) {
					this.graph.style.height = (parseInt(this.graph.style.height, 10) + ((skew - 9) / 4)) + 'px';
					line.style.marginTop = (12 + ((skew * 1.5) - 6)) + 'px';
					this.label.style.paddingTop = (20 + skew) + 'px';
					this.container.style.width = (this.width + 100 + skew) + 'px'
				}*/
				
				this.addHover(this.stops[i], clone, time);
				
			} else {

				if (subline.children.length > 0) {
					
					nwidth = 0;
					
					if (this.stops[i - 1]) {
	
						this.stops[i - 1].block.appendChild(subline);
						children = this.stops[i - 1].block.children[0].children;
						
						for (var x = 0; x < children.length; x += 1) {
							child = children[x]
							if (child.className) {
								nwidth += parseFloat(child.style.width);
								child.style.width = parseFloat(child.style.width).toFixed(0) + 'px';
							}
						}
						
						subline.style.width = nwidth + 'px';
						subline.style.marginLeft = '-' + (nwidth / 2) + 'px'; 
					}
					
					subline = document.createElement('div');
					subline.className = 'tgraph-subline';
				}
				
				this.addHover(this.stops[i], this.stops[i].block, time);
			}
			
			
			line.appendChild(this.stops[i].block);
			
			time += this.stops[i].duration;
			laststate = this.stops[i]['label'];
			
		}
		
		if (!document.addEventListener) {
			var clear = document.createElement('div');
			clear.style.clear = 'both';
			line.appendChild(clear);
		}
		
		this.graph.appendChild(line);
		
	}
	
};
