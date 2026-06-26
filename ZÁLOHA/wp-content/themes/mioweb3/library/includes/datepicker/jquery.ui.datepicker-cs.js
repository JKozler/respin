jQuery(function ($) {
	$.datepicker.regional['cs'] = {
		closeText: datepicker_texts.close,
		prevText: '&#x3c;' + datepicker_texts.sooner,
		nextText: datepicker_texts.later + '&#x3e;',
		currentText: datepicker_texts.now,
		monthNames: [datepicker_texts.january, datepicker_texts.february, datepicker_texts.march, datepicker_texts.april, datepicker_texts.may, datepicker_texts.june,
			datepicker_texts.july, datepicker_texts.august, datepicker_texts.september, datepicker_texts.october, datepicker_texts.november, datepicker_texts.december],
		monthNamesShort: [datepicker_texts.jan, datepicker_texts.feb, datepicker_texts.mar, datepicker_texts.apr, datepicker_texts.ma, datepicker_texts.jun,
			datepicker_texts.jul, datepicker_texts.aug, datepicker_texts.sep, datepicker_texts.oct, datepicker_texts.nov, datepicker_texts.dec],
		dayNames: [datepicker_texts.sunday, datepicker_texts.monday, datepicker_texts.tuesday, datepicker_texts.wednesday, datepicker_texts.thursday, datepicker_texts.friday, datepicker_texts.saturday],
		dayNamesShort: [datepicker_texts.su, datepicker_texts.mo, datepicker_texts.tu, datepicker_texts.we, datepicker_texts.th, datepicker_texts.fr, datepicker_texts.sa],
		dayNamesMin: [datepicker_texts.su, datepicker_texts.mo, datepicker_texts.tu, datepicker_texts.we, datepicker_texts.th, datepicker_texts.fr, datepicker_texts.sa],
		weekHeader: datepicker_texts.week_short,
		dateFormat: datepicker_texts.date_format,
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	};
	$.datepicker.setDefaults($.datepicker.regional['cs']);
});


//jQuery(document).ready(function ($) {
	//$('.cms_datepicker').datepicker({dateFormat: "dd.mm.yy"});
//});
