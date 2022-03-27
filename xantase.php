<?php 

if (! function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        $needle_len = strlen($needle);
        return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, - $needle_len));
    }
}

/**
 * This is the common exception class for known errors for Xantase interpeting
 */
class XantaseException extends Exception{
    protected $message = "";

    function __construct($mess){
        $this->message = $mess;
    }

    function appendToMessage($st){
        $this->message .= $st;
    }


}

class Xantase {

    /**
     * Build xantase files in directory and output in file
     * @param String $dir directory where all the files are
     * @param String $output put the file here 
     * @return bool true when ok and false on fail
     * @throws XantaseException on interpetation error or location error
     */
    public function xantase_build_output_to_file(String $dir,String $output): bool {
        return file_put_contents($output,$this->xantase_build($dir))!==false;
    }

    /**
     * Build the Xantase stuff to a string
     * @param String $dir the sourcefiles directory
     * @return String get compiled string
     * @throws XantaseException on interpetation error or location error
     */
    public function xantase_build(String $dir): String{
        $result = "";
        $result .= $this->xantase_get_base_classes();
        $result .= $this->xantase_interpetate_dir($dir);
        return $result;
    }

    /**
     * Get base classes for Xantase
     * @return String the base class string
     */
    public function xantase_get_base_classes(): String{
        return "
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
        ";
    }

    /**
     * Interpetate directory
     * @param String directory where the files are
     * @return String the interpetated file
     * @throws XantaseException if dir is not found and interpetate errors
     */
    public function xantase_interpetate_dir(String $dirdir): String{
        $result = "";
        if(!file_exists($dirdir)){
            throw new XantaseException("Directory does not exists: $dirdir ");
        }
        if(!is_dir($dirdir)){
            throw new XantaseException("This is not a directory: $dirdir ");
        }
        foreach(scandir($dirdir) as $f){
            if($f=="."){
                continue;
            }
            if($f==".."){
                continue;
            }
            if(str_ends_with($f,".xs")){
                $result .= "\n\n\n";
                $result .= $this->xantase_interpetate_file( $dirdir . DIRECTORY_SEPARATOR . $f );
            }
        }
        return $result;
    }

    /**
     * Opens a file and then interpetates it, returning a string as result
     * @param String $file The absolute path to the file
     * @return String the interpetated document
     * @throws XantaseException when the file is not found or when there is interpetation errors
     */
    public function xantase_interpetate_file(String $file): String{
        if(!str_ends_with($file,".xs")){
            throw new XantaseException("Fileextension is not .xs");
        }
        $filecontents = file_get_contents($file);
        if($filecontents===false){
            throw new XantaseException("Unable to open file: $file");
        }
        return $this->xantase_interpetate_string($filecontents,basename($file,".xs"));
    }

    /**
     * Make from a tokenstring an array
     * @param String $tok the line to process
     * @return Array the tokenarray
     */
    private function xantase_tokenise_string(String $tok): Array{
        $result = Array();
        $tok = trim($tok);
        if(!empty($tok)){
            $toks = str_split($tok);
            $buffer = "";
            $isstring = false;
            foreach($toks as $to){
                if($to===' '&&!$isstring&&!empty($buffer)){
                    array_push($result,Array("isstring" => $isstring,"contents" => $buffer));
                    $buffer = "";
                }else if($to=='"'){
                    if(!empty($buffer)){
                        array_push($result,Array("isstring" => $isstring,"contents" => $buffer));
                        $buffer = "";
                    }
                    $isstring = $isstring===false;
                }else{
                    $buffer .= $to;
                }
            }
            if(!empty($buffer)){
                array_push($result,Array("isstring" => $isstring,"contents" => $buffer));
            }
        }
        return $result;
    }

    private $hasbuild = false;

    private function xantase_builder_gen_func(XantaseException $xe,Array $lines): String{
        $datset = "";
        if(count($lines)<=2){
            $xe->appendToMessage("Function not defined as we expected!");
        }
        $functionname = $lines[1]["contents"];
        if($functionname==="build"){
            $this->hasbuild = true;
        }
        $argx = Array();
        if(count($lines)>=2){
            $withident = $lines[2]["contents"];
            if($withident!="with"){
                $xe->appendToMessage("Expected with");
            }
            for($i = 3 ; $i < count($lines) ; $i++){
                array_push($argx,$lines[$i]["contents"]);
            }
        }
        $datset .= $functionname . "(" . implode(", ",$argx) . ") {";
        return $datset;
    }

    private function xantase_builder_gen_create(XantaseException $xe,Array $lines): String{
        $res = "";
        if(!count($lines)>=5){
            $xe->appendToMessage("CREATE invalid formed");
            throw $xe;
        }
        $subtype = $lines[1]["contents"];
        $type = $lines[2]["contents"];
        $called = $lines[3]["contents"];
        $name = $lines[4]["contents"];
        if($called!="called"){
            $xe->appendToMessage("Expected: called");
            throw $xe;
        }
        if($type=="variable"){
            $res .= "var $name";
            if(count($lines)>5){
                $res .= ";\n\t\t";
                $and = $lines[5]["contents"];
                if($and!="and"){
                    $xe->appendToMessage("Expected: and ");
                    throw $xe;
                }
                $st = array_splice($lines,6);
                $res .= $this->xantase_builder_gen_set($xe,$st,$name);
            }else{
                $res .= ";";
            }            
        }else if($type=="node"){
            $res .= "var $name = document.createElement(\"$subtype\")";
            if(count($lines)>5){
                $xe->appendToMessage("Command length exeed for type node");
                throw $xe;
            }
            $res .= ";";
        }
        return $res;
    }

    private function xantase_builder_gen_set(XantaseException $xe,Array $lines,?String $varname): String{
        // set property innerHTML of h1elem to gingerbread
        $result = "";
        $i = 0;
        $set_stat = $lines[$i++]["contents"];
        if($set_stat!="set"){
            $xe->appendToMessage("Expected: set");
            throw $xe;
        }
        $stat_stat = $lines[$i++]["contents"];
        $stat_oper = null;
        if($stat_stat=="property"){
            $stat_oper = $lines[$i++]["contents"];
        }else if($stat_stat!="value"){
            $xe->appendToMessage("Invalid type [$stat_stat]");
            throw $xe;
        }
        $of_stat = $lines[$i++]["contents"];
        if($of_stat=="of"){
            $varname = $lines[$i++]["contents"];
            $of_stat = $lines[$i++]["contents"];
        }
        if($of_stat!="to"){
            $xe->appendToMessage("Expected: to");
            throw $xe;
        }
        $rs = $this->xantase_builder_gen_res($xe,array_splice($lines,$i));
        $result .= "$varname";
        if(isset($stat_oper)){
            $result .= ".".$stat_oper;
        }
        $result .= " = $rs";
        $result .= ";";
        return $result;
    }

    private function xantase_builder_gen_res(XantaseException $xe,Array $lines): String{
        $result = "";
        $tw = $lines[0];
        if($tw["isstring"]){
            $result .= "\"";
            $result .= $tw["contents"];
            $result .= "\""; 
        }else if(is_numeric($tw["contents"])){
            $result .= $tw["contents"];
        }else{
            $result .= "" . $tw["contents"] . "";
        }
        return $result;
    }

    private function xantase_builder_gen_call(XantaseException $xe,Array $lines): String{
        $result = "";
        // call appendChild of rootdoc with gingerbread
        $i = 1;
        $parent = $lines[$i++]["contents"];
        $child = null;
        $args = Array();
        if($i!=count($lines)){
            $cmd= $lines[$i++]["contents"];
            if($cmd=="of"){
                $child = $parent;
                $parent = $lines[$i++]["contents"];
                if($i!=count($lines)){
                    $cmd= $lines[$i++]["contents"];
                }
            }
            if($i!=count($lines)&&$cmd=="with"){
                for($t = $i ; $t < count($lines) ; $t++){
                    array_push($args,$lines[$t]["contents"]);
                }
            }
        }
        $result .= $parent;
        if(isset($child)){
            $result .= "." . $child;
        }
        $result .= "(" . implode(", ",$args) . ");";
        return $result;
    }

    /**
     * Creates the classcode for the created String
     * @param String $command_string the string we need to interpetate
     * @param String $classname the name of the class we going to make
     * @return String the interpetated file
     * @throws XantaseException when there is a error in the code 
     */
    public function xantase_interpetate_string(String $command_string,String $classname): String{
        $datset = "";
        $this->hasbuild = false;
        if(empty($command_string)){
            throw new XantaseException("Empty string for template $classname ");
        }
        $lineset = explode("\n",$command_string);
        $linebuffer = $lineset;
        foreach($lineset as $lineno => $line){
            $lineset[$lineno] = $this->xantase_tokenise_string($line);
        }
        $withtab = false;
        foreach($lineset as $linenumber => $lines){
            if(empty($lines)){
                continue;
            }
            $xe = new XantaseException("Error in class $classname line " . ($linenumber + 1) . ":  " . $linebuffer[$linenumber] . " :: ");
            $prima = $lines[0];
            if($prima["isstring"]){
                $xe->appendToMessage($xe->getMessage() . "First token in a line cannot be a string");
                throw $xe;
            }
            $datset .= ($withtab?"\t":"") . "\t// " . $linebuffer[$linenumber] . "\n\t" . ($withtab?"\t":"");
            switch($prima["contents"]){
                case "function":
                    // create a function....
                    $datset .= $this->xantase_builder_gen_func($xe,$lines);
                    $withtab = true;
                    break;
                case "create":
                    // create a variable....
                    $datset .= $this->xantase_builder_gen_create($xe,$lines);
                    break;
                case "set":
                    // set a variable....
                    $datset .= $this->xantase_builder_gen_set($xe,$lines,null);
                    break;
                case "call":
                    // call a function
                    $datset .= $this->xantase_builder_gen_call($xe,$lines);
                    break;
                case "if":
                    break;
                case "end":
                    $datset .= "}";
                    $withtab = false;
                    break;
                default:
                    $xe->appendToMessage("Unknown token: " . $prima["contents"]);
                    throw $xe;
                    break;
            }
            $datset .= "\n";
        }

        if(!$this->hasbuild){
            throw new XantaseException("$classname has no build function!!");
        }

        $result = "class $classname extends XantaseBuildable{\n\t" . $datset . "\n}";
        return $result;
    } 
}

?>