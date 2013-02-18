Ext.namespace('CB');

CB.About = Ext.extend(Ext.Window, {
	
	initComponent: function() {
		var m='amlioto:el@gublrca.aocm';
		var mo='';
		for(var i=0;i<m.length;i++,i++){mo=mo+m.substring(i+1,i+2)+m.substring(i,i+1)}
    
		m='amliotv:tilaeit.ruacung@amlic.mo';
		var mv='';
		for(var i=0;i<m.length;i++,i++){mv=mv+m.substring(i+1,i+2)+m.substring(i,i+1)}
		
		var s = '<div style="text-align:center"><a target="_blank" href="http://www.burlaca.com/"><img src="css/i/CaseBox-Logo-medium.png" width="470" height="88" title="CaseBox" alt="CaseBox" /></a></div>';
		s += '<div style="line-height: 1.7"><p>'+L.Authors+': <a href="'+mo+'">Oleg Burlaca</a>, ';
		s += '<a href="'+mv+'">Vitalie Țurcanu</a></p>';
		s += '<p>Homepage: <a onclick="window.open(this.href); return false;" href="http://www.burlaca.com/">www.burlaca.com</a></p>';
		s += '<div style="color: #777; line-height: 1.2; padding: 15px 0px">';
		s += 'Copyright (C) 2011  Oleg Burlaca<br />';
		s += '</div>';
		s += 'Iconpack: <a onclick="window.open(this.href); return false;" href="http://www.pinvoke.com/">Fugue Icons</a><br /><br />';
		s += 'Powered By:';
		s += '<table style="width: 100%; background-color: #fff"><tr>';
		s += '<td><a onclick="window.open(this.href); return false;" href="http://www.extjs.com/"><img src="css/i/logo_extjs.png" width="90" height="22" alt="ExtJS - A foundation you can build on" title="ExtJS - A foundation you can build on" /></a></td>';
		s += '<td style="text-align: center"><a onclick="window.open(this.href); return false;" href="http://www.php.net"><img src="css/i/logo_php.gif" width="44" height="26" alt="PHP - Hypertext Preprocessor" title="PHP - Hypertext Preprocessor" /></a></td>';
		s += '<td style="text-align: right"><a onclick="window.open(this.href); return false;" href="http://mysql.com/"><img src="css/i/logo_mysql.gif" width="65" height="29" alt="MySql - The world\'s most popular open source database" title="MySql - The world\'s most popular open source database" /></a></td>';
		s += '</tr></table>';
		
		Ext.apply(this, {
			title: 'About CaseBox',
			plain: true,
			closable: true,
			iconCls: 'icon-about',
			height: 350,//380,
			width: 500,
			bodyStyle: 'padding: 10px',
			layout: 'fit',
			border: false,
			html: s,
			resizable: false,
			buttonAlign: 'center',
			buttons: [
				{text: 'OK', handler: function() {this.close()}, scope: this} // iconCls: 'icon-ok',
			]
		});
		
		CB.About.superclass.initComponent.apply(this, arguments);
	}
	
})
