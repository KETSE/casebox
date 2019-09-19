Ext.namespace('Ext.ux');

Ext.define('Ext.ux.fileMD5', {
	extend: 'Ext.util.Observable'
	,chunkSize: 2097152

	,constructor: function(){
		this.blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;
		this.fileReader = new FileReader();
		this.fileReader.onload = this.frOnload.bind(this);
		this.fileReader.onerror = this.frOnerror.bind(this);

		Ext.ux.fileMD5.superclass.constructor.apply(this, arguments);
	}

	,getMD5: function(file) {
		this.file = file;
		this.chunks = Math.ceil(this.file.size / this.chunkSize);
		this.currentChunk = 0;
		this.spark = new SparkMD5.ArrayBuffer();
		this.md5 = null;
		this.loadNext();
	}

	,frOnload: function(e){
		this.spark.append(e.target.result); // append array buffer
		this.currentChunk++;

		if (this.currentChunk < this.chunks) {
			this.loadNext();
		}else{
			this.md5 = this.spark.end();
			delete this.spark;
			this.fireEvent('done', this, this.md5);
		}
	}

	,frOnerror: function () {
		delete this.spark;
		return null;
	}

	,loadNext: function() {
		var start = this.currentChunk * this.chunkSize,
		end = ((start + this.chunkSize) >= this.file.size) ? this.file.size : start + this.chunkSize;
		this.fileReader.readAsArrayBuffer(this.blobSlice.call(this.file, start, end));
	}
});
