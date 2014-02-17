
initdemo1CustomFunctionality= function(){
	demo1HelperFunction('dummy value');
	App.on('objectinit', function(objectWindow){
		if(Ext.isEmpty(objectWindow.plugins)) objectWindow.plugins = [new demo1.customizeObjectClass()];
		else objectWindow.plugins.push(this);
	})
}


demo1HelperFunction = function(param1){
	clog('make something with param1', param1);
}

Ext.onReady(initdemo1CustomFunctionality)
