Ext.namespace('CB');

/**
 * Base class for processing Drag and Drop requests
 *
 * On this class relies other Drang and Drop plugins created for Panels, Trees, Grids etc.
 * An instance of CB.DD class will be crated in App.DD
 *     and any component would be able to listen to DD events through this instance.
 */

Ext.define('CB.DD', {
    extend: 'Ext.util.Observable'
    ,data: []
    ,action: 'copy' // copy / move / shortcut

    ,constructor: function(config){
        CB.DD.superclass.constructor.call(this, config);
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
     * }
     *     @param  function|null callback
     *     @param  object|null   scope
     * @return void
     */
    ,execute: function (params, callback, scope){
        if(Ext.isObject(params.action)) {
            params.action = this.detectActionFromEvent(params.action);
        }
        if(callback) {
            this.callback = scope ? Ext.Function.bind(callback, scope) : callback;
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
                : params.sourceData.length + ' objects'
                ,targetName = Ext.valueFrom(params.targetData.name, params.targetData.title);

            Ext.Msg.confirm(
                L.Confirmation
                ,L['DDActionMsg_' + params.action].replace('{source}', sourceText).replace('{target}', targetName)
                ,this.onConfirmExecution
                ,this
            );
        } else {
            this.onConfirmExecution('yes');
        }
    }
    /**
     * confirm action execution function
     * @param  varchar b pressed button text
     * @return void
     */
    ,onConfirmExecution: function (b){
        if(b != 'yes'){
            return;
        }
        this.fireEvent('beforeexecute', this.params);
        CB_Browser_Actions[this.params.action](this.params, this.processExecute, this);
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
    /**
     * processing execution
     * @param  json r server responce
     * @param  event e event
     * @return void
     */
    ,processExecute: function(r, e){

        if(r.success !== true){
            if(r.confirm === true) {
                Ext.Msg.confirm(L.Confirmation, r.msg, function(b){
                    if(b == 'yes'){
                        this.params.confirmedOverwrite = true;
                        this.onConfirmExecution('yes');
                    }
                }, this);
            } else {
                Ext.Msg.alert(L.Error, Ext.valueFrom(r.msg, L.ErrorOccured));
            }
        } else {
            Ext.copyTo(r, this.params, 'sourceData,targetData');
            r.targetId = r.targetData.id;
            App.fireEvent('objectsaction', this.params.action, r, e);
        }

        if(this.callback) {
            this.callback(r.pids);
            delete this.callback;
        }
    }
}
);
