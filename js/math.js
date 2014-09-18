



Array.prototype.binarySearch = function binarySearch(find, comparator) {
  var low = 0, high = this.length - 1,
      i, comparison;
  while (low <= high) {
    i = parseInt((low + high) / 2, 10);
    comparison = comparator(this[i], find);
    if (comparison < 0) { low = i + 1; continue; };
    if (comparison > 0) { high = i - 1; continue; };
    return i;
  }
  return null;
};


/*
Permutations without Repetition
n! / (n-r)!
*/


var map = [0, 1, 2, 3];
function perm_count(n, r){
    var perm = 1, len = n>>>0, last = isNaN(r) ? 0 : len - r>>>0;
    while (len > last) perm*=len--;
    return perm;
}


function permutations_count (n/*number of elements*/, k/*number of selected elements*/) {
    var perm = 1, n = n>>>0, k = isNaN(k) ? 0 : n - k>>>0;
    while (n > k) perm*=n--;
    return perm;
}

function combinations_count(n/*number of elements*/, k/*number of selected elements*/){
	if (k == 0 || k == n)  return 1;
	if (k > n || k < 0)  return 0;
    return permutations_count(n, k)/permutations_count(k);
}



var map = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63];

function perm_list(source){
	function perm_fun(remaining, base, arr){
		if (remaining.length == 1) {
			base.push(remaining[0]);
			arr[arr.length] = base;
		} else {
			for(var j = 0, len = remaining.length; j < len; j++) {
				currchar = remaining[j];
				//if(remaining.indexOf(currchar) == j)
					arr = perm_fun(remaining.slice(0, j).concat(remaining.slice(j+1)), base.slice(0).concat([currchar]), arr);
			 }
		}
		return arr;
	}
	return perm_fun(source, [], [])
}


function comb_count(n, r){
	if ((r == 0) || (r == n))  return 1;
	if ((r > n) || (r < 0))  return 0;
    return perm_count(n, r)/perm_count(r);
}

function comb_list(array, r) {
	var result = [];
    function equal(a, b) {
        for (var i = 0; i < a.length; i++) {
            if (a[i] != b[i]) return false;
        }
        return true;
    }
    function values(i, a) {
        var ret = [];
        for (var j = 0; j < i.length; j++) ret.push(a[i[j]]);
        return ret;
    }
    var n = array.length;
    var indices = [];
    for (var i = 0; i < r; i++) indices.push(i);
    var final = [];
    for (var i = n - r; i < n; i++) final.push(i);
    while (!equal(indices, final)) {
		result.push( values(indices, array) )
        var i = r - 1;
        while (indices[i] == n - r + i) i -= 1;
        indices[i] += 1;
        for (var j = i + 1; j < r; j++) indices[j] = indices[i] + j - i;
    }
	result.push( values(indices, array) )
	return result;
}

var combs = comb_list(map, 4), chr = String.fromCharCode, clen = combs.length, bits, perms, plen, row, text, r = /^[a-z =]+$/i;

while (clen--) {
	perms = perm_list( combs[clen] ), plen = perms.length;
	while ( row = perms[--plen] ) {
		pos = 0
		bits = row[0]<<18 | row[1]<<12 | row[2]<<6 | row[3];
		text = chr(bits>>16 & 0xff, bits>>8 & 0xff, bits & 0xff);
		if (r.test(text)) console.log(clen, plen, text, row);
	}
}

var len = map.length, perm = perm_count(len), i = len;
while (perm--) {
	++i %= len;

}

perm_list('abc'.split(''), [], [])


function perm_one(perm, step, len){
	var out = [];

	for (var b=len,div=perm;b>r; b--) {
		div/=b;
		var index = (step/div)%b;
		out.push(index)
	}
	return out;
}

var perm = perm_count(4);

perm_one(perm,0,4);



perm(64,8) = 178462987637760

eJztPWmT27iVnze/
eJztPWmT


var str, r = /^[a-z]+$/i
function roll(arr) {
    var len = arr.length;
    arr.unshift(arr[len-1])
    arr.length = len;
    return arr;
}
var arr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'.split(''), len = arr.length;
while (--len) {
    base64_map = roll(arr).join('');
	str = Ex.base64_decode('eJztPWmT27iV');
	if (r.test(str)) console.log(str,base64_map)
}

var str, r = /^[a-z]+$/i
var input = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
var perm=1, digits=input.length;
for (var i=1;i<=digits;perm*=i++);
for (var a=0;a<perm;a++){
	var avail=input, out = [];
 
		for (var b=digits,div=perm;b>0; b--) {
			div/=b;
			var index = (a/div)%b;
			out.push(input.charAt(index))
		}
		base64_map = out.join('');
	str = Ex.base64_decode('eJztPWmT27iV');
	if (r.test(str)) console.log(str,base64_map)
}


var perm=1, digits=3;
for (var i=1;i<=digits;perm*=i++);
perm



    function factorial(n) {
        var result = 1;
        for (var i = 2; i <= n; i++) {
            result *= i
        }
        return result;
    }
