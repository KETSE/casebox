
initCustomFunctionality= function(){
	App.on('browserinit', function(browser){
        browser.cardContainer.add({
            xtype: 'DemosrcViewGraph'
            ,owner: browser
            ,addDivider: true
        });
	});
};

Ext.onReady(initCustomFunctionality);
