
                class XantaseBuildable{
                    build(data){
                        throw new Error('Invalid class');
                    }
                }

                class Xantase extends XantaseBuildable{

                    constructor(){
                        super();
                    }
                    build(classname,objecttobindto,data){
                        console.info('Buildcommand triggered of '+classname.name+' and placed in '+objecttobindto,data);
                        if(typeof(classname)!=='function'){
                            throw new Error('Invalid class');
                        }
                        if(!(Object.getPrototypeOf(classname.prototype.constructor).name === 'XantaseBuildable')){
                            throw new Error('Not an Xantase object');
                        }
                    }
                }
        


class HelloWorld extends XantaseBuildable{
            
        }