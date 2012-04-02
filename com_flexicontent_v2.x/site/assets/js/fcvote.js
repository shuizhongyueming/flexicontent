window.addEvent('domready', function() {
	if($$('.fcvote').length > 0) {
		$$('.fcvote a').addEvent('click', function(e) {
			e = new Event(e).stop();
			
			var data_arr = this.getProperty('rel').split("_");
			var itemID = data_arr[0];
			// Extra voting option
			if (typeof(data_arr[1])!="undefined" && data_arr[1]) {
				xid = data_arr[1];
			} else {
				var xid = "main";  // default to ... 'main' voting
			}
			
			var log = $('fcvote_cnt_' + itemID + '_' + xid).empty().addClass('ajax-loader');
			
			if(MooTools.version>="1.2.4") {
				
				var rating = this.get('text');
				var voteurl = getBaseURL() + "index.php?option=com_flexicontent&format=raw&task=ajaxvote&user_rating=" + rating + "&cid=" + itemID + "&xid=" + xid;
				var jsonRequest = new Request.JSON({
					url: voteurl,
					onSuccess: function(data){
						if (typeof(data.percentage)!="undefined" && data.percentage)
							$('rating_' + itemID + '_' + xid).setStyle('width', data.percentage + "%");
						$('fcvote_cnt_' + itemID + '_' + xid).set('html', data.html).removeClass('ajax-loader');
						setTimeout(function() {
							$('fcvote_cnt_' + itemID + '_' + xid).set('html', data.htmlrating);
						}, 2000);
					}
				}).send();
				
			} else {
				
				var rating = this.innerHTML;
				var voteurl = getBaseURL() + "index.php?option=com_flexicontent&format=raw&task=ajaxvote&user_rating=" + rating + "&cid=" + itemID + "&xid=" + xid;
				var ajax = new Ajax(voteurl, {
					onComplete: function(data){
						data=Json.evaluate(data);
						if (typeof(data.percentage)!="undefined" && data.percentage)
							$('rating_' + itemID + '_' + xid).setStyle('width', data.percentage + "%");
						$('fcvote_cnt_' + itemID + '_' + xid).removeClass('ajax-loader');
						$('fcvote_cnt_' + itemID + '_' + xid).innerHTML=data.html;
						setTimeout(function() {
							$('fcvote_cnt_' + itemID + '_' + xid).innerHTML=data.htmlrating;
						}, 2000);
					}
				});
				ajax.request();
				
			}
			
		});
	}

});

function getBaseURL() {
	var url = location.href;  // entire url including querystring - also: window.location.href;
	var baseURL = url.substring(0, url.indexOf('/', 14));

	return baseURL + sfolder + "/";
}