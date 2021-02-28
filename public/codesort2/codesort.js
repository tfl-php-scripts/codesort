function checkAll(checked) {
    var boxes = document.getElementsByTagName('input');
    var boxesLen = boxes.length
    for (i = 0; i < boxesLen; i++) {
        var e = boxes[i];
        if ((e.type=='checkbox') && (!e.disabled) ) {
            e.checked = checked;
        }
    }
}

function setAllSize(value) {
    var selects = getElementsByClass('setSize', null, 'select');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i ++) {
        selects[i].value = value;
    }
}

function setAllCat(value) {
    var selects = getElementsByClass('setCat', null, 'select');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i ++) {
        selects[i].value = value;
    }
}

function setAllDonor(value) {
    var selects = getElementsByClass('setDonor', null, 'select');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i ++) {
        selects[i].value = value;
    }
}

function setAllAppr(checked) {
    var selects = getElementsByClass('setAppr', null, 'input');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i ++) {
        selects[i].checked = checked;
    }
}

function toggleAllExtra() {
    var selects = getElementsByClass('toggleExtra', null, 'div');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i ++) {
        toggle(selects[i].id);
    }
}

function toggle(id) {
    var el = document.getElementById(id);
    if ( el.style.display != 'none' ) {
        el.style.display = 'none';
    } else {
        el.style.display = 'block';
    }
}

function getElementsByClass(searchClass,node,tag) {
    var classElements = new Array();
    if ( node == null )
        node = document;
    if ( tag == null )
        tag = '*';
    var els = node.getElementsByTagName(tag);
    var elsLen = els.length;
    var pattern = new RegExp('(^|\\s)'+searchClass+'(\\s|$)');
    for (i = 0, j = 0; i < elsLen; i++) {
        if ( pattern.test(els[i].className) ) {
            classElements[j] = els[i];
            j++;
        }
    }
    return classElements;
}