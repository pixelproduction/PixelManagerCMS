function trim (str, charlist) {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: mdsjack (http://www.mdsjack.bo.it)
  // +   improved by: Alexander Ermolaev (http://snippets.dzone.com/user/AlexanderErmolaev)
  // +      input by: Erkekjetter
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +      input by: DxGx
  // +   improved by: Steven Levithan (http://blog.stevenlevithan.com)
  // +    tweaked by: Jack
  // +   bugfixed by: Onno Marsman
  // *     example 1: trim('    Kevin van Zonneveld    ');
  // *     returns 1: 'Kevin van Zonneveld'
  // *     example 2: trim('Hello World', 'Hdle');
  // *     returns 2: 'o Wor'
  // *     example 3: trim(16, 1);
  // *     returns 3: 6
  var whitespace, l = 0,
    i = 0;
  str += '';

  if (!charlist) {
    // default list
    whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
  } else {
    // preg_quote custom list
    charlist += '';
    whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
  }

  l = str.length;
  for (i = 0; i < l; i++) {
    if (whitespace.indexOf(str.charAt(i)) === -1) {
      str = str.substring(i);
      break;
    }
  }

  l = str.length;
  for (i = l - 1; i >= 0; i--) {
    if (whitespace.indexOf(str.charAt(i)) === -1) {
      str = str.substring(0, i + 1);
      break;
    }
  }

  return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}


function strnatcmp (f_string1, f_string2, f_version) {
  // http://kevin.vanzonneveld.net
  // +   original by: Martijn Wieringa
  // + namespaced by: Michael White (http://getsprink.com)
  // +    tweaked by: Jack
  // +   bugfixed by: Onno Marsman
  // -    depends on: strcmp
  // %          note: Added f_version argument against code guidelines, because it's so neat
  // *     example 1: strnatcmp('Price 12.9', 'Price 12.15');
  // *     returns 1: 1
  // *     example 2: strnatcmp('Price 12.09', 'Price 12.15');
  // *     returns 2: -1
  // *     example 3: strnatcmp('Price 12.90', 'Price 12.15');
  // *     returns 3: 1
  // *     example 4: strnatcmp('Version 12.9', 'Version 12.15', true);
  // *     returns 4: -6
  // *     example 5: strnatcmp('Version 12.15', 'Version 12.9', true);
  // *     returns 5: 6
  var i = 0;

  if (f_version == undefined) {
    f_version = false;
  }

  var __strnatcmp_split = function (f_string) {
    var result = [];
    var buffer = '';
    var chr = '';
    var i = 0,
      f_stringl = 0;

    var text = true;

    f_stringl = f_string.length;
    for (i = 0; i < f_stringl; i++) {
      chr = f_string.substring(i, i + 1);
      if (chr.match(/\d/)) {
        if (text) {
          if (buffer.length > 0) {
            result[result.length] = buffer;
            buffer = '';
          }

          text = false;
        }
        buffer += chr;
      } else if ((text == false) && (chr == '.') && (i < (f_string.length - 1)) && (f_string.substring(i + 1, i + 2).match(/\d/))) {
        result[result.length] = buffer;
        buffer = '';
      } else {
        if (text == false) {
          if (buffer.length > 0) {
            result[result.length] = parseInt(buffer, 10);
            buffer = '';
          }
          text = true;
        }
        buffer += chr;
      }
    }

    if (buffer.length > 0) {
      if (text) {
        result[result.length] = buffer;
      } else {
        result[result.length] = parseInt(buffer, 10);
      }
    }

    return result;
  };

  var array1 = __strnatcmp_split(f_string1 + '');
  var array2 = __strnatcmp_split(f_string2 + '');

  var len = array1.length;
  var text = true;

  var result = -1;
  var r = 0;

  if (len > array2.length) {
    len = array2.length;
    result = 1;
  }

  for (i = 0; i < len; i++) {
    if (isNaN(array1[i])) {
      if (isNaN(array2[i])) {
        text = true;

        if ((r = this.strcmp(array1[i], array2[i])) != 0) {
          return r;
        }
      } else if (text) {
        return 1;
      } else {
        return -1;
      }
    } else if (isNaN(array2[i])) {
      if (text) {
        return -1;
      } else {
        return 1;
      }
    } else {
      if (text || f_version) {
        if ((r = (array1[i] - array2[i])) != 0) {
          return r;
        }
      } else {
        if ((r = this.strcmp(array1[i].toString(), array2[i].toString())) != 0) {
          return r;
        }
      }

      text = false;
    }
  }

  return result;
}


function strnatcasecmp (str1, str2) {
  // http://kevin.vanzonneveld.net
  // +      original by: Martin Pool
  // + reimplemented by: Pierre-Luc Paour
  // + reimplemented by: Kristof Coomans (SCK-CEN (Belgian Nucleair Research Centre))
  // + reimplemented by: Brett Zamir (http://brett-zamir.me)
  // +      bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +         input by: Devan Penner-Woelk
  // +      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // *        example 1: strnatcasecmp(10, 1);
  // *        returns 1: 1
  // *        example 1: strnatcasecmp('1', '10');
  // *        returns 1: -1
  var a = (str1 + '').toLowerCase();
  var b = (str2 + '').toLowerCase();

  var isWhitespaceChar = function (a) {
    return a.charCodeAt(0) <= 32;
  };

  var isDigitChar = function (a) {
    var charCode = a.charCodeAt(0);
    return (charCode >= 48 && charCode <= 57);
  };

  var compareRight = function (a, b) {
    var bias = 0;
    var ia = 0;
    var ib = 0;

    var ca;
    var cb;

    // The longest run of digits wins.  That aside, the greatest
    // value wins, but we can't know that it will until we've scanned
    // both numbers to know that they have the same magnitude, so we
    // remember it in BIAS.
    for (var cnt = 0; true; ia++, ib++) {
      ca = a.charAt(ia);
      cb = b.charAt(ib);

      if (!isDigitChar(ca) && !isDigitChar(cb)) {
        return bias;
      } else if (!isDigitChar(ca)) {
        return -1;
      } else if (!isDigitChar(cb)) {
        return 1;
      } else if (ca < cb) {
        if (bias === 0) {
          bias = -1;
        }
      } else if (ca > cb) {
        if (bias === 0) {
          bias = 1;
        }
      } else if (ca === '0' && cb === '0') {
        return bias;
      }
    }
  };

  var ia = 0,
    ib = 0;
  var nza = 0,
    nzb = 0;
  var ca, cb;
  var result;

  while (true) {
    // only count the number of zeroes leading the last number compared
    nza = nzb = 0;

    ca = a.charAt(ia);
    cb = b.charAt(ib);

    // skip over leading spaces or zeros
    while (isWhitespaceChar(ca) || ca === '0') {
      if (ca === '0') {
        nza++;
      } else {
        // only count consecutive zeroes
        nza = 0;
      }

      ca = a.charAt(++ia);
    }

    while (isWhitespaceChar(cb) || cb === '0') {
      if (cb === '0') {
        nzb++;
      } else {
        // only count consecutive zeroes
        nzb = 0;
      }

      cb = b.charAt(++ib);
    }

    // process run of digits
    if (isDigitChar(ca) && isDigitChar(cb)) {
      if ((result = compareRight(a.substring(ia), b.substring(ib))) !== 0) {
        return result;
      }
    }

    if (ca === '0' && cb === '0') {
      // The strings compare the same.  Perhaps the caller
      // will want to call strcmp to break the tie.
      return nza - nzb;
    }

    if (ca < cb) {
      return -1;
    } else if (ca > cb) {
      return +1;
    }

    // prevent possible infinite loop
    if (ia >= a.length && ib >= b.length) return 0;

    ++ia;
    ++ib;
  }
}
