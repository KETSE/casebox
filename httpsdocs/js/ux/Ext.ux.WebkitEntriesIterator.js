Ext.namespace('Ext.ux');

Ext.ux.WebkitEntriesIterator = {
    // imageTypes: ['image/png', 'image/jpeg', 'image/gif'],

    //readEntries(entries, parentNode);
    iterateEntries: function(entries, callback, scope){
        this.direcotoriesCount = 0;
        this.result = [];
        this.callback = scope ?  callback.bind(scope) : callback;
        this.readEntries(entries);

    }
    // Recursive directory read
    ,readEntries: function(entries, fromSubfolder) {
        if(fromSubfolder) {
            this.direcotoriesCount--;
        }

        for (var i = 0; i < entries.length; i++) {
            if(!Ext.isEmpty(entries[i])) {
                if (entries[i].isDirectory) {
                    this.direcotoriesCount++;
                    // appendItem(entries[i].name, 'folder', parentNode);
                    var directoryReader = entries[i].createReader();
                    this.getAllEntries( directoryReader, this.readEntries.bind(this) );
                } else {
                    this.result.push(entries[i]);
                    // appendItem(entries[i].name, 'file', parentNode);
                    // entries[i].file(appendFile, errorHandler);
                }
            }
        }
        if(this.direcotoriesCount === 0){
            this.convertEntriesToFiles();
            //this.callback(this.result);
        }
    }

    // This is needed to get all directory entries as one
    // call of readEntries may not return all items. Works a
    // bit like stream reader.
    ,getAllEntries: function (directoryReader, callback) {
        var entries = [];

        var readEntries = function () {
            directoryReader.readEntries(function (results) {
                if (!results.length) {
                    entries.sort();
                    this.direcotoriesCount--;
                    callback(entries, true);
                } else {
                    entries = entries.concat(Array.prototype.slice.call(results || [], 0));
                    readEntries();
                }
            }, this.errorHandler);
        };

        readEntries();
    }

    ,errorHandler: function (e) {
        console.log('FileSystem API error code: ' + e.code);
    }

    ,convertEntriesToFiles: function(){
        this.convertedFiles = 0;

        var fn = function(f){
            f.fullPath = this.result[this.convertedFiles].fullPath;
            this.result[this.convertedFiles] = f;
            this.convertedFiles++;
            if(this.convertedFiles == this.result.length) {
                this.callback(this.result);
            }
        };

        for (var i = 0; i < this.result.length; i++) {
            this.result[i].file(
                fn.bind(this)
            );
        }
    }

};
