
class XantaseBuildable{
	build(data){
		throw new Error('Invalid class');
	}
}

class Xantase extends XantaseBuildable{
	
	constructor(){
		super();
	}
	
	data = null;
	
	setData(d){
		this.data = d;
	}
	
	getData(){
		return this.data;
	}
	
	build(classname,objecttobindto,data){
		console.info('Buildcommand triggered of '+classname.name+' and placed in '+objecttobindto,data);
		if(typeof(classname)!=='function'){
			throw new Error('Invalid class');
		}
		if(!(Object.getPrototypeOf(classname.prototype.constructor).name === 'XantaseBuildable')){
			throw new Error('Not an Xantase object');
		}
		if(typeof(objecttobindto)==='string'){
			objecttobindto = document.getElementById(objecttobindto);
		}
		objecttobindto.innerHTML = '';
		var us = new classname();
		us.build(objecttobindto,this.getData(),data);
	}
}



class HelloWorld extends XantaseBuildable{
	base = null;
	// function build with rootdoc data params
	build(rootdoc, data, params) {
		this.base = rootdoc;
		// create h1 node called h1elem
		var h1elem = document.createElement("h1");
		this.base.appendChild(h1elem);
		// create string variable called gingerbread and set value to "This is an example of xantase!"
		var gingerbread;
		gingerbread = "This is an example of xantase!";
		// set property innerHTML of h1elem to gingerbread
		h1elem.innerHTML = gingerbread;
		// foreach params as pew for spawn ListItem on rootdoc using pew
		for (let pew of params) {
			(new ListItem()).build(rootdoc, data, pew);
		}
		// end function
	}
	
}


class ListItem extends XantaseBuildable{
	base = null;
	// function build with rootdoc data params
	build(rootdoc, data, params) {
		this.base = rootdoc;
		// create div node called lid
		var lid = document.createElement("div");
		this.base.appendChild(lid);
		// create h1 node called uname on lid
		var uname = document.createElement("h1");
		lid.appendChild(uname);
		// set property style.backgroundColor of uname to "green"
		uname.style.backgroundColor = "green";
		// create string variable called liw
		var liw = "";
		// set value of liw from call concat of liw with params.id "# " params.name
		liw = liw.concat(params.id, "# ",  params.name);;
		// set property innerHTML of uname to liw
		uname.innerHTML = liw;
		// end function
	}
	
}
