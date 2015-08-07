Ext.override(Ext.Ajax,{

    initDisplayMsg: function() {
        this.displayMsgTimeout = 1500; //1.5sec

        this.displayMsgTask = new Ext.util.DelayedTask(
            this.onDisplayMsgDelay
            ,this
            ,[]
            ,false
        );
        this.loadingMsgDiv = App.getNotificationDiv();
        this.loadingMsgDiv.update('<div class="content">' +  Ext.LoadMask.prototype.msg + '</div>');
    }

    ,request: function(options) {
        var request = this.callParent(arguments);

        if(request) {
            request.startTime = new Date();
        }

        if(!this.displayMsgTask) {
            this.initDisplayMsg();
        }

        this.displayMsgTask.delay(this.displayMsgTimeout);

        return request;
    }

    ,onDisplayMsgDelay: function() {
        var requests = this.requests
            ,id
            ,empty = true
            ,time = new Date()
            ,displayMsg = false;


        //check active requests
        for (id in requests) {
            if (requests.hasOwnProperty(id)) {
                if(Ext.isEmpty(requests[id].timedout) &&
                    ((time - requests[id].startTime) < 6000) //skip failed requests
                ) {
                    empty = false;
                    if(time - requests[id].startTime > this.displayMsgTimeout) {
                        displayMsg = true;
                    }
                }
            }
        }

        if(empty) {
            this.displayMsgTask.cancel();
            this.loadingMsgDiv.hide();

        } else if(displayMsg) {
            this.displayMsgTask.delay(this.displayMsgTimeout);

            this.loadingMsgDiv.show();
            this.loadingMsgDiv.getEl().fadeIn();

            this.loadingMsgDiv.task.delay(3000);
        }
    }
});
