
initSampleCustomFunctionality= function(){
	SampleHelperFunction('dummy value');
	App.on('objectinit', function(objectWindow){
		if(Ext.isEmpty(objectWindow.plugins)) objectWindow.plugins = [new Sample.customizeObjectClass()];
		else objectWindow.plugins.push(this);
	})
}


SampleHelperFunction = function(param1){
	clog('make something with param1', param1);
}

Ext.onReady(initSampleCustomFunctionality)
