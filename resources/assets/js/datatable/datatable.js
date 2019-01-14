import $ from 'jquery';
import JSZip from 'jszip';

import {fnUpdateDataTable} from './api';
import defaults from './defaults';

/**
 * Knoop alle datatable plugins aan jquery.
 */
import 'datatables.net';
import 'datatables.net-autofill';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.colVis';
import 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.flash';
import 'datatables.net-buttons/js/buttons.print';
import 'datatables.net-colreorder';
import 'datatables.net-fixedcolumns';
import 'datatables.net-fixedheader';
import 'datatables.net-keytable';
import 'datatables.net-responsive';
import 'datatables.net-scroller';
import 'datatables.net-select';
import '../lib/dataTables.childRow';
import '../lib/dataTables.columnGroup';

import './buttons';

// Excel button in datatables.net-buttons/js/buttons.html5 checkt voor JSZip in window.
window.JSZip = JSZip;

$.extend(true, $.fn.dataTable.defaults, defaults);

/**
 * Wordt gebruikt in gesprekken.
 *
 * @param {jQuery} $table
 */
const fnAutoScroll = $table => {
	let $scroll = $table.parent();
	if ($scroll.hasClass('dataTables_scrollBody')) {
		// autoscroll if already on bottom
		if ($scroll.scrollTop() + $scroll.innerHeight() >= $scroll[0].scrollHeight - 20) {
			// check before draw and scroll after
			window.setTimeout(() => {
				$scroll.animate({
					scrollTop: $scroll[0].scrollHeight
				}, 800);
			}, 200);
		}
	}
};

export const fnGetLastUpdate = $table => () => Number($table.data('lastupdate'));
export const fnSetLastUpdate = $table => lastUpdate => $table.data('lastupdate', lastUpdate);

/**
 * Called after ajax load complete.
 *
 * @returns object
 * @param {jQuery} $table
 */
export const fnAjaxUpdateCallback = $table => json => {
	fnSetLastUpdate(json.lastUpdate);
	const tableConfig = $table.DataTable();

	if (json.autoUpdate) {
		const timeout = parseInt(json.autoUpdate);
		if (!isNaN(timeout) && timeout < 600000) { // max 10 min
			setTimeout(() => {
				$.post(tableConfig.ajax.url(), {
					'lastUpdate': fnGetLastUpdate($table)
				}, data => {
					fnUpdateDataTable($table.attr('id'), data);
					fnAjaxUpdateCallback($table)(data);
				});
			}, timeout);
		}
	}

	fnAutoScroll($table);

	return json.data;
};

$(() => {
	$('body').on('click', () => {
		// Verwijder tooltips als de datatable modal wordt gesloten
		$('.ui-tooltip-content').parents('div').remove();
	});
});