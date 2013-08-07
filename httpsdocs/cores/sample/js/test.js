
initDevCustomFunctionality= function(){
	DevHelperFunction('dummy value');
	App.on('objectinit', function(objectWindow){
		if(Ext.isEmpty(objectWindow.plugins)) objectWindow.plugins = [new Dev.customizeObjectClass()];
		else objectWindow.plugins.push(this);
	})
}


DevHelperFunction = function(param1){
	clog('make something with param1', param1);
}

Ext.onReady(initDevCustomFunctionality)
