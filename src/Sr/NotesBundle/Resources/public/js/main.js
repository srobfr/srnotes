function checkNote(urlWs, checked, noteId) {
    var data = {
        id: noteId,
        pcAvancement: (checked?100:0)
    };

    $.ajax(urlWs, {
        type: "POST",
        data: data,
        success: function(data) {
            $('.tr' + noteId).css('text-decoration', (checked ? 'line-through' : ''));
        }
    });
}

function checkNxt(urlWs, checked, noteId) {
    var data = {
        id:noteId,
        prochaine:(checked ? 'true' : 'false')
    };

    $.ajax(urlWs, {
        type:"POST",
        data:data,
        success:function (data) {
            $('.tr' + noteId).css('background-color', (checked ? '#FFFFB0' : ''));
        }
    });
}

function checkWtg(urlWs, checked, noteId) {
    var data = {
        id:noteId,
        enAttente:(checked ? 'true' : 'false')
    };

    $.ajax(urlWs, {
        type:"POST",
        data:data,
        success:function (data) {
            $('.tr' + noteId).css('background-color', (checked ? '#EAEAEA' : ''));
        }
    });
}

jQuery.fn.extend({
    insertAtCaret:function (myValue) {
        return this.each(function (i) {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            }
            else if (this.selectionStart || this.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
        })
    }
});

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function () {
    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        var dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        var _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d:d,
                dd:pad(d),
                ddd:dF.i18n.dayNames[D],
                dddd:dF.i18n.dayNames[D + 7],
                m:m + 1,
                mm:pad(m + 1),
                mmm:dF.i18n.monthNames[m],
                mmmm:dF.i18n.monthNames[m + 12],
                yy:String(y).slice(2),
                yyyy:y,
                h:H % 12 || 12,
                hh:pad(H % 12 || 12),
                H:H,
                HH:pad(H),
                M:M,
                MM:pad(M),
                s:s,
                ss:pad(s),
                l:pad(L, 3),
                L:pad(L > 99 ? Math.round(L / 10) : L),
                t:H < 12 ? "a" : "p",
                tt:H < 12 ? "am" : "pm",
                T:H < 12 ? "A" : "P",
                TT:H < 12 ? "AM" : "PM",
                Z:utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o:(o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S:["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default":"ddd mmm dd yyyy HH:MM:ss",
    shortDate:"m/d/yy",
    mediumDate:"mmm d, yyyy",
    longDate:"mmmm d, yyyy",
    fullDate:"dddd, mmmm d, yyyy",
    shortTime:"h:MM TT",
    mediumTime:"h:MM:ss TT",
    longTime:"h:MM:ss TT Z",
    isoDate:"yyyy-mm-dd",
    isoTime:"HH:MM:ss",
    isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames:[
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames:[
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
    return dateFormat(this, mask, utc);
};

var gsTab = "    "; // select tab char : tab or spaces
var gsReturn = "\n";

function insertTab(e) {
    var key = e.keyCode ? e.keyCode : e.charCode ? e.charCode : e.which;

    if (key == 9 && !e.ctrlKey && !e.altKey) {
        var isFF = !(e.preventDefault == undefined); // verify if or not firefox
        var o = ((isFF) ? e.target : e.srcElement);
        var iTop = o.scrollTop; // for anti-scroll in firefox
        if (!e.shiftKey) {
            (isFF) ? setIndentFF(o, e, true) : setIndentIE(o, e, true);
        } else {
            (isFF) ? setIndentFF(o, e, false) : setIndentIE(o, e, false);
        }
        e.returnValue = false;
        if (isFF) {
            e.preventDefault();
        }
        o.focus();
        o.scrollTop = iTop;
        return false;
    }
    return true;
}
function setIndentFF(o, e, bAdd) {
    var sFull = o.value, sSel = '', sNew = '', iStart, iEnd;
    iStart = o.selectionStart;
    iEnd = o.selectionEnd;
    sSel = sFull.substring(iStart, iEnd);
    if (sSel.length) {
        iStart = sFull.lastIndexOf(gsReturn, iStart) + 1;
        sSel = sFull.substring(iStart, iEnd);
        sNew = bAdd ? getTabAddedStr(sSel) : getTabDeletedStr(sSel);
        o.value = sFull.substring(0, iStart) + sNew + sFull.substring(iEnd);
        o.setSelectionRange(iStart, iStart + sNew.length);
    } else if (bAdd) {
        o.value = sFull.substring(0, iStart) + gsTab + sFull.substring(iEnd);
        o.setSelectionRange(iStart + gsTab.length, iStart + gsTab.length);
    }
}
function setIndentIE(o, e, bAdd) {
    var rng, sFull, sSel, iStart, iEnd, sNew, iStartRes;
    rng = document.selection.createRange();
    sFull = o.value;
    sSel = rng.text;
    if (sSel.length) {
        iStartRes = getSelStartIE(o); // sel start first point
        iStart = sFull.lastIndexOf(gsReturn, iStartRes) + 1; // new sel start point
        rng.moveStart("character", iStart - iStartRes);
        rng.moveEnd("character", -1); // tric pour IE
        rng.select();
        sSel = rng.text;
        sNew = bAdd ? getTabAddedStr(sSel) : getTabDeletedStr(sSel);
        rng.text = sNew;
        rng.collapse(false);
        rng.moveStart("character", -sNew.length + sNew.split(gsReturn).length - 1);
        rng.moveEnd("character", 1); // tric pour IE
        rng.select();
    } else if (bAdd) {
        rng.text = gsTab;
        rng.select();
    }
}
function getTabDeletedStr(sTmp) {
    var aSel = sTmp.split(gsReturn);
    // tab = 4 spaces
    var aRex = Array(/^\t/, /^ {4}/, /^ {1,3}\t*/);
    for (var i = 0, sLine; i < aSel.length; i++) {
        sLine = aSel[i];
        for (var j = 0; j < 3; j++) {
            if (sLine.match(aRex[j])) {
                aSel[i] = sLine.replace(aRex[j], '');
                break;
            }
        }
    }
    return aSel.join(gsReturn);
}
function getTabAddedStr(sTmp) {
    aSel = sTmp.split(gsReturn);
    for (var i = 0; i < aSel.length; i++) {
        aSel[i] = gsTab + aSel[i];
    }
    return aSel.join(gsReturn);
}
function getSelStartIE(textarea) {
    var r = document.selection.createRange();
    var lensel = r.text.length;
    // Move selection start to 0 position.
    r.moveStart('character', -textarea.value.length);
    // The caret position is selection length
    return r.text.length - lensel;
}

function timeToCountDown(time) {
    var r = '';
    d = Math.floor(time / (60*60*24)); time -= d*60*60*24;
    h = Math.floor(time / (60*60)); time -= h*60*60;
    m = Math.floor(time / 60); time -= m*60;
    s = time;
    if(d > 0) r += ('0' + d).substr(-2) + 'j ';
    if(d > 0 || h > 0) r += ('0' + h).substr(-2) + ':';
    if(d > 0 || h > 0 || m > 0) r += ('0' + m).substr(-2) + ':';
    r += ('0' + s).substr(-2);
    return r;
}