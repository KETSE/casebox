
Ext.namespace('CB');

/**
 * Base class for processing Drag and Drop requests
 *
 * On this class relies other Drang and Drop plugins created for Panels, Trees, Grids etc. 
 * An instance of CB.DD class will be crated in App.DD 
 *     and any component would be able to listen to DD events through this instance.
 */

CB.DD = Ext.extend(Ext.util.Observable, {
    data: []
    ,action: 'copy' // copy / move / shortcut
    ,constructor: function(config){
        this.addEvents({
            'beforeexecute': true
            ,'execute': true
            ,'copied': true
            ,'moved': true
            ,'shortcuted': true
        });
        CB.Clipboard.superclass.constructor.call(this, config)
    }
    /**
     * Execute a Drag and Drop operation
     * object  params {  
     *     @param  varchar/event   action   'copy' | 'move' | 'shortcut'. 
     *             When drag event is passed - the action will be guessed from the event, relying on pressed keys.
     *             Shift - move
     *             Ctrl - copy
     *             Alt - shortcut
     *             <none> - default "move" action
     *     @param  object   targetData     generic data for target object
     *     @param  object/array   sourceData     generic data for source object(s)
     *     @param  boolean   confirm     set to false wen you don't need a confirmation dialog for the action. Default to true
     *     @param  function|null callback 
     *     @param  object|null   scope   
     * }
     * @return void
     */
    ,execute: function (params){
        return Ext.Msg.alert(L.Info, 'This action will work on next commit. Reviewing right now.');
        if(Ext.isObject(params.action)) {
            params.action = this.detectActionFromEvent(params.action)
        }
        switch(params.action){
            case 'copy':
                this.postEvent = 'copied';
                break;
            case 'move':
                this.postEvent = 'moved';
                break;
            case 'shortcut':
                this.postEvent = 'shortcuted';
                break;
            default: 
                return Ext.Msg.alert('Error', 'CB.DD: Invalid action specified for execute');
        }

        if(!Ext.isArray(params.sourceData)){
            params.sourceData = [params.sourceData];
        } 
        this.params = params;
        if(params.confirm !== false){
            var sourceText = (params.sourceData.length == 1)
                ? params.sourceData[0].name
                : params.sourceData.length + ' objects';
            Ext.Msg.confirm(
                L.Confirmation
                ,L['DDActionMsg_' + params.action].replace('{source}', sourceText).replace('{target}', params.targetData.name)
                ,this.onConfirmExecution
                ,this
            )
        } else this.onConfirmExecution('yes');
    }
    ,onConfirmExecution: function (b){
        if(b != 'yes'){
            return;
        }
        this.fireEvent('beforeexecute', r.pids);
        CB_Browser.paste(this.lastParams, this.processPaste, this);
    }
    /**
     * detect desired action from event
     * @param  object event 
     * @return varchar       
     */
    ,detectActionFromEvent: function(event){
        if(event.ctrlKey) {
            return 'copy';
        } else if(event.altKey) {
            return 'shortcut';
        }
        return 'move';
    }
    ,processExecute: function(r, e){
        this.fireEvent('execute', r.pids);
        
        if(r.success !== true){
        }else{
            this.fireEvent('pasted', r.pids);
        }
        if(this.callback) this.callback(r.pids);
    }
}
)

Ext.reg('CBDD', CB.DD);