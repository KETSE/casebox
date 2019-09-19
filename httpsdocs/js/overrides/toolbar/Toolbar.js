/*
    Overrides
*/

Ext.override(Ext.toolbar.Toolbar, {

    hideInutilSeparators: function() {
        // return;
        var vi = []; //visible items

        //collect all visible items
        this.items.each(
            function(i, idx, len){
                if(i.isVisible()) {
                    vi.push(i);
                }
            }
            ,this
        );

        //now iterate the array and hide tbsplitters at the begining,
        //at the end, consecutive, before and after spacer.
        for (var i = 0; i < vi.length; i++) {
            if(vi[i].xtype === 'tbseparator') {
                vi[i].setHidden(
                    (i === 0) || // at the begining
                    (i == (vi.length-1)) || // at the end
                    (vi[i+1].xtype === 'tbfill') || // before tbfill
                    (vi[i-1].xtype === 'tbfill') || // after tbfill
                    (vi[i-1].xtype === 'tbseparator') // after another tbseparator
                );
            }
        }
    }
});
