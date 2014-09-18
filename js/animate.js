


/* File: animate.js
   Animation functions.

   About: License
   "THE BEER-WARE LICENSE" (Revision 42)

   <lauri@neti.ee> wrote this file. As long as you retain this notice you
   can do whatever you want with this stuff. If we meet some day, and you
   think this stuff is worth it, you can buy me a beer in return. */


$fx=(function(){

	var fx=function(el){
		if(typeof(el)=='string')el=document.getElementById(el);
		if(el.nodeType&&el.nodeType==1){
			this.tick=0;
			this.interval=20;
		}
		return this;

	}

	fx.prototype={
		add:function(){
			this.running=true;
			return this;
		},
		run:function(){
			if(!this.running){
				this.running=true;
				this.tick=setInterval(queue_run,this.interval);

			}
			return this;
		},
		pause:function(){
			this.running=false;
			return this;
		},
		stop:function(){
			remove(this);
			return this;
		}
	}
	return function(){
		return new fx();
	}
})();

(function(){
	var tick,queue=[];
	var queue_run=function(){
		for(i=0;queue[i];queue[i].running&&queue[i++].run());
	}
	var c1={},c2={},c3={},c4={},c5={};
	var styles={
		linear:function(x){return x;},
		sin:function(x){return c1[x]||(c1[x]=Math.sin(x*Math.PI/2));},
		pulse:function(x){return c2[x]||(c2[x]=(Math.sin(x*2*Math.PI)+1)/2);},
		bounce:function(x){return c3[x]||(c3[x]=(x==0)?0:(((-Math.sin(x*25)/(x*25))+1)*(1-x))+x);},
		bounceLess:function(x){return c4[x]||(c4[x]=(x==0)?0:(((-Math.sin(x*15)/(x*15))+1)*(1-x))+x);},
		bounceMore:function(x){return c5[x]||(c5[x]=(x==0)?0:(((-Math.sin(x*35)/(x*35))+1)*(1-x))+x);}
	}


	/**
	 * parse a color to be handled by the animation, supports hex and rgb (#FFFFFF, #FFF, rgb(255, 0, 0))
	 * @param {String} str The string value of an elements color
	 * @return {Array} The rgb values of the color contained in an array
	 */
	var parseColor = function(str){
		var rgb = str.match(/^#?(\w{2})(\w{2})(\w{2})$/);
		if(rgb && rgb.length == 4){
			return [parseInt(rgb[1], 16), parseInt(rgb[2], 16), parseInt(rgb[3], 16)];
		}

		rgb = str.match(/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/);
		if(rgb && rgb.length == 4){
			return [parseInt(rgb[1], 10), parseInt(rgb[2], 10), parseInt(rgb[3], 10)];
		}
		rgb = str.match(/^#?(\w{1})(\w{1})(\w{1})$/);
		if(rgb && rgb.length == 4){
			return [parseInt(rgb[1] + rgb[1], 16), parseInt(rgb[2] + rgb[2], 16), parseInt(rgb[3] + rgb[3], 16)];
		}
	};



	var effects={
		fade:function(element, params, style, speed, sleep, loop, post_hook){
			var opacity1=params.from||0;
			var opacity2=params.to||1;
			return function(){
				var st=this.elem.style;
				if(st){
					st.opacity = opacity1 + this.style(this.step/100) * (opacity2-opacity1);
					st.filter = "alpha(opacity=" + st.opacity*100 + ")";
				}
			}
		},
		changeStyle:function(element, params, style, speed, sleep, loop, post_hook){
			var what=params.what||'height';
			var suffix=params.suffix||'';
			var value1=params.value1||'';
			var value2=params.value2||'';
			return function(){
				this.elem.style[what] = Math.round(value1 + this.style(this.step/100)*(value2-value1)) + suffix;
			}
		}
	};

	var remove=function(effect){
		effect.post_hook&&effect.post_hook();
		var i=queue.indexOf(effect)||-1;
		if(i>-1)queue.splice(i,1);
		if(queue.length==0){
			clearInterval(tick);
			tick=0;
		}
	}

	var runner=function(elem, action, style, speed, sleep, loop, post_hook){

		this.elem=typeof(elem)==='string'?$(elem):elem;

		this.sleep=sleep||0;
		this.speed = speed;
		this.running = true;
		this.step = 0;
		this.action = action;
		this.post_hook = post_hook;
		this.loop = loop||false;
		this.style = (style&&styles[style]) ? styles[style]:styles['sin'];

		this.run=function(){
			if(this.sleep>0){this.sleep--;return;}
			if(!this.elem){remove(this);return;}

			if(this.step>99){
				this.step=100;
				this.action();
				if(this.loop){
					this.step=0;
				}else{
					remove(this);
				}
			}else{
				this.action();
				this.step+=this.speed;
			}
		}
		this.kill=function(){
			remove(this);
		}
		queue.push(this);
		if(!tick){
			tick=setInterval(queue_run,40);//40µs ~ 25fps
		}
		return this;
	}

	runner.prototype={
		pause:function(){
			this.running=false;
		},
		stop:function(){
			remove(this);
		}
	}



	window.animate=function(obj,effect,style,speed,p){
		if(!p)p={};
		if(effects[effect]){
			var action=effects[effect](obj,p);
			return new runner(obj,action,style||'linear',speed||10,p.sleep||0,p.loop||false,p.post_hook||false);
		}else{
			p.post_hook&&p.post_hook();
		}
	}

	window.animate.register=function(effect,anim){
		effects[effect]=anim;
	}

})();


