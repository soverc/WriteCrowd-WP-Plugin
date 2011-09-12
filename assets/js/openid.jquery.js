(function(jQ)
{
	jQ.openID = {};
		
		jQ.openID.authenticate = function(obj)
		{
			
		};
		
		jQ.openID._iframe = function(obj)
		{
			var ifr = '<iframe src="' + obj.src + '" style="display:none"></iframe>';
				document.write(ifr);
		};
		
	jQ.googleID = {};
	
		jQ.googleID.authenticate = function(obj)
		{
			
		};
		
		jQ.googleID._iframe = function(obj)
		{
			var ifr = '<iframe src="' + obj.src + '" style="display:none"></iframe>';
				document.write(ifr);
		};
})(jQuery);