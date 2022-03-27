
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
                        var us = new classname();
                        us.build(objecttobindto,this.getData(),data);
                    }
                }
        


class HelloWorld extends XantaseBuildable{
		// function build with rootdoc data params
	build(rootdoc, data, params) {
		// create h1 node called h1elem
		var h1elem = document.createElement("h1");
		// create string variable called gingerbread and set value to "This is an example of xantase!"
		var gingerbread;
		gingerbread = "This is an example of xantase!";
		// set property innerHTML of h1elem to gingerbread
		h1elem.innerHTML = gingerbread;
		// call appendChild of rootdoc with h1elem 
		rootdoc.appendChild(h1elem);
		// create HelloWorld variable called ins
		var ins;
		// if ewe of params is 1 then call build of ins with rootdoc data
		
		// end function
		}

}