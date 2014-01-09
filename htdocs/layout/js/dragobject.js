/**
 * dragobject.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery
 */

$(document).ready(function() {
	window.addEventListener('mousedown', startDrag, false);
	window.addEventListener('mouseup', stopDrag, false);
});

var dragobjectID = false;
var oldX;
var oldY;

function startDrag(e) {
	e = e || window.event;
	var tag = e.target.tagName.toUpperCase();
	var overflow = $(e.target).css('overflow');
	if (tag === 'SELECT' || tag === 'INPUT' || tag === 'TEXTAREA' || overflow === 'auto' || overflow === 'scroll') { // sliding scrollbar of dropdown menu or input field
		return;
	}
	dragobjectID = $(e.target).attr('id');
	if (typeof dragobjectID === 'undefined' || dragobjectID === false || !$('#'+dragobjectID).hasClass('dragobject')) {
		dragobjectID = $(e.target).closest('.dragobject').attr('id');
	}
	if (typeof dragobjectID !== 'undefined' && dragobjectID !== false) {
		oldX = mouseX(e);
		oldY = mouseY(e);
		window.addEventListener('mousemove', mouseMoveHandler, true);
	}
	else {
		dragobjectID = false;
	}
}
function stopDrag(e) {
	if (!dragobjectID) {
		return;
	}
	window.removeEventListener('mousemove', mouseMoveHandler, true);
	$.post('/tools/dragobject.php', {
		id: dragobjectID,
		coords: {
			left: dragobjLeft(),
			top: dragobjTop()
		}
	});
	dragobjectID = false;
}
function mouseMoveHandler(e) {
	if (!dragobjectID) {
		return;
	}
	e = e || window.event;
	var newX = mouseX(e);
	var newY = mouseY(e);
	$('#'+dragobjectID).css('left', (dragobjLeft() + newX - oldX) + 'px');
	$('#'+dragobjectID).css('top', (dragobjTop() + newY - oldY) + 'px');
	oldX = newX;
	oldY = newY;
}
function docScrollLeft() {
	return (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
}
function docScrollTop() {
	return (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
}
function mouseX(e) {
	if (e.pageX) {
	  return e.pageX;
	}
	if (e.clientX) {
		return e.clientX + docScrollLeft();
	}
	return null;
}
function mouseY(e) {
	if (e.pageY) {
		return e.pageY;
	}
	if (e.clientY) {
		return e.clientY + docScrollTop();
	}
	return null;
}
function dragobjLeft() {
	return $('#'+dragobjectID).offset().left - docScrollLeft();
}
function dragobjTop() {
	return $('#'+dragobjectID).offset().top - docScrollTop();
}