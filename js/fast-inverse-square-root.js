
/*
http://en.wikipedia.org/wiki/Fast_inverse_square_root
http://cautionsingularityahead.blogspot.com/2010/04/javascript-and-ieee754-redux.html
*/


var pow23 = Math.pow(2, 23)
  , pow126 = Math.pow(2, -126)
function fromIEEE754(i){
	var f = i&0x7fffff, e = (i>>23)&0xff, s = (i>>31) ? -1 : 1;
	if (e === 255) {
		return f !== 0 ? NaN : s * Infinity;
	}
	return s * Math.pow(2, e - 127) * (1 + f / pow23);
}

function toIEEE754(f){
	var v = Math.abs(f);

	if (v >= pow126) {
		var ln = Math.min(Math.floor(Math.log(v) / Math.LN2), 127);
		e = ln + 127;
		f = v * Math.pow(2, 23 - ln) - pow23;
	}
	else {
		e = 0;
		f = v / Math.pow(2, 1 - 127 - 23);
	}
	return (f<0?1:0)<<31 | e<<23 | f
}

function invSqrt(f){
	var x = fromIEEE754(1597463007 - (toIEEE754(f)>>1));
    return x*(1.5 - f*.5*x*x);
}

