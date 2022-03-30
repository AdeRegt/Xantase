<?php 

if (! function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        $needle_len = strlen($needle);
        return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, - $needle_len));
    }
}

if (! function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && strpos($haystack, $needle) !== false;
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

    private $classname;
    private $linenumber;
    private function report_error(String $message): void{
        throw new XantaseException("
            An error occured:
            - Message: $message

            - Class: " . $this->classname . "
            - Linenumber: " . ($this->linenumber + 1) . "
        ");
    }

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

        $empt = explode("\n",$result);
        $spac = Array();
        foreach($empt as $e){
            array_push($spac,trim($e));
        }

        $result = "";
        $ewe = 0;
        foreach($spac as $s){
            if(str_contains($s,"}")){
                $ewe--;
            }
            for($i = 0 ; $i < $ewe ; $i++){
                $result .= "\t";
            }
            $result .= $s . "\n";
            if(str_contains($s,"{")){
                $ewe++;
            }
        }
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
                            objecttobindto = document.querySelector(objecttobindto);
                        }
                        objecttobindto.innerHTML = '';
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
        $toks = str_split(trim($tok));
        $buffer = "";
        $isstring = false;
        foreach($toks as $to){
            if($to===' '&&!$isstring){
                if(!empty($buffer)){
                    array_push($result,Array("isstring" => $isstring,"contents" => $buffer));
                }
                $buffer = "";
            }else if($to=='"'){
                if($isstring||!empty($buffer)){
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
        return $result;
    }

    private $hasbuild = false;

    private function xantase_builder_gen_func(Array $lines): String{
        $datset = "";
        if(count($lines)<=2){
            $this->report_error("Function not defined as we expected!");
        }
        $functionname = $lines[1]["contents"];
        if($functionname==="build"){
            $this->hasbuild = true;
        }
        $argx = Array();
        if(count($lines)>=2){
            $withident = $lines[2]["contents"];
            if($withident!="with"){
                $this->report_error("Expected with");
            }
            for($i = 3 ; $i < count($lines) ; $i++){
                array_push($argx,$lines[$i]["contents"]);
            }
        }
        if($functionname==="build"&&count($argx)!=3){
            $this->report_error("The build function requires 3 parameters");
        }
        $datset .= $functionname . "(" . implode(", ",$argx) . ") {";
        if($functionname==="build"){
            $datset .= "\nthis.base = " . $argx[0] . ";";
        }
        return $datset;
    }

    private function xantase_builder_gen_create(Array $lines): String{
        $res = "";
        if(!count($lines)>=5){
            $this->report_error("CREATE invalid formed");
        }
        $subtype = $lines[1]["contents"];
        $type = $lines[2]["contents"];
        $called = $lines[3]["contents"];
        $name = $lines[4]["contents"];
        if($called!="called"){
            $this->report_error("Expected: called");
        }
        if($type=="variable"){
            $res .= "var $name";
            if(count($lines)>5){
                $res .= ";\n";
                $and = $lines[5]["contents"];
                if($and!="and"){
                    $this->report_error("Expected: and ");
                }
                $st = array_splice($lines,6);
                $res .= $this->xantase_builder_gen_set($st,$name);
            }else{
                if($subtype=="string"){
                    $res .= " = \"\"";
                }else if($subtype=="number"){
                    $res .= " = 0";
                }else{
                    $res .= " = null";
                }
                $res .= ";";
            }            
        }else if($type=="node"){
            $res .= "var $name = document.createElement(\"$subtype\")";
            $edic = "this.base";
            if(count($lines)==7){
                $on = $lines[5]["contents"];
                $target = $lines[6]["contents"];
                if($on!="on"){
                    $this->report_error("Expected: on");
                }
                $edic = $target;
            }else if(count($lines)!=5){
                $this->report_error("Command length exeed for type node");
            }
            $res .= ";\n" . $edic . ".appendChild(" . $name . ");";
        }else if($type=="query"){
            $res .= "var $name = document.querySelector(\"$subtype\");";
        }
        return $res;
    }

    private function xantase_builder_gen_set(Array $lines,?String $varname): String{
        $result = "";
        $i = 0;
        $set_stat = $lines[$i++]["contents"];
        if($set_stat!="set"){
            $this->report_error("Expected: set");
        }
        $stat_stat = $lines[$i++]["contents"];
        $stat_oper = null;
        if( $stat_stat=="property" || $stat_stat=="listener" ){
            $stat_oper = $lines[$i++]["contents"];
        }else if($stat_stat!="value"){
            $this->report_error("Invalid type [$stat_stat]");
        }
        $of_stat = $lines[$i++]["contents"];
        if($of_stat=="of"){
            $varname = $lines[$i++]["contents"];
            $of_stat = $lines[$i++]["contents"];
        }
        $rs = "null";
        if($of_stat=="to"){
            $rs = $this->xantase_builder_gen_res(array_splice($lines,$i));
        }else if($of_stat=="from"){
            $rs = $this->xantase_builder_gen_call(array_splice($lines,$i));
        }else{
            $this->report_error("Expected: to or from");
        }
        if($stat_stat=="listener"){
            $result .= $varname . ".addEventListener('" . $stat_oper . "'," . $rs . ")";
        }else{
            $result .= "$varname";
            if(isset($stat_oper)){
                $result .= ".".$stat_oper;
            }
            $result .= " = $rs";
        }
        $result .= ";";
        return $result;
    }

    private function xantase_builder_gen_res(Array $lines): String{
        
        $result = "";
        if(empty($lines)){
            $this->report_error("Value expected");
        }
        $tw = $lines[0];
        if($tw["isstring"]){
            $result .= "\"";
            $result .= $tw["contents"];
            $result .= "\""; 
            if(count($lines)>1){
                if($lines[1]["contents"]=="&"){
                    $beol = false;
                    for($i = 1 ; $i < count($lines) ; $i++){
                        if($lines[$i]["contents"]=="&"){
                            $result .= " + ";
                            $beol = true;
                        }else if($beol){
                            $beol = false;
                            if($lines[$i]["isstring"]){
                                $result .= "\"" . $lines[$i]["contents"] . "\"";
                            }else{
                                $result .= $lines[$i]["contents"];
                            }
                        }else{
                            $this->report_error("Unexpected statement");
                        }
                    }
                }else{
                    $this->report_error("Unexpected statement");
                }
            }
        }else if(is_numeric($tw["contents"])){
            $result .= $tw["contents"];
        }else{
            $result .= "" . $tw["contents"] . "";
        }
        return $result;
    }

    private function xantase_builder_gen_args(Array $lines): Array{
        $args = Array();
        for($t = 0 ; $t < count($lines) ; $t++){
            $deze = $lines[$t];
            if($deze["isstring"]){
                array_push($args,"\"" . $deze["contents"] . "\"");
            }else{
                array_push($args,$deze["contents"]);
            }
        }
        return $args;
    }

    private function xantase_builder_gen_call(Array $lines): String{
        $result = "";
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
                $args = $this->xantase_builder_gen_args(array_slice($lines,$i));
            }
        }
        $result .= $parent;
        if(isset($child)){
            $result .= "." . $child;
        }
        $result .= "(" . implode(", ",$args) . ");";
        return $result;
    }

    private function xantase_builder_gen_spawn(Array $lines): String{
        $result = "";
        $classname = $lines[1]["contents"];
        $on = $lines[2]["contents"];
        if($on!="on"){
            $this->report_error("Expected: on");
        }
        $base = $lines[3]["contents"];
        $on = $lines[4]["contents"];
        if($on!="using"){
            $this->report_error("Expected: using");
        }
        $params = $lines[5]["contents"];
        $result = "(new $classname()).build($base, data, $params);";
        return $result;
    }

    private function xantase_builder_gen_foreach(Array $lines): String{
        $result = "";
        // foreach params as pew for 
        $attA = $lines[1]["contents"];
        $attB = $lines[2]["contents"];
        $attC = $lines[3]["contents"];
        $attD = $lines[4]["contents"];
        if($attB!="as"){
            $this->report_error("Expected: as");
        }
        if($attD!="for"){
            $this->report_error("Expected: for");
        }
        $result .= "for (let $attC of $attA) {\n";
        $result .= $this->xantase_builder_line(array_splice($lines,5)) . "\n";
        $result .= "}";
        return $result;
    }

    private function xantase_builder_gen_js(Array $lines): String{
        $awa = Array();
        foreach(array_slice($lines,1) as $lx){
            if($lx["isstring"]){
                array_push($awa,"\"" . $lx["contents"] . "\"");
            }else{
                array_push($awa,$lx["contents"]);
            }
        }
        return implode(" ",$awa);
    }

    private function xantase_builder_gen_if(Array $lines): String{
        $result = "";
        $commands = Array();
        $isand = 0;
        for($i = 1 ; $i < count($lines) ; $i++){

            // find operators that has 2 words and merge them into 1 line
            $keywords = array('greater', 'less');
            foreach($keywords as $keyword){
                $key = array_search($keyword, array_column($lines, 'contents'));  
                if($key !== false){    
                    foreach($lines as $index => &$line){  
                        $nextOneExists = array_key_exists($index + 1, $lines);
                        if($index == $key){
                            $line['contents'] = $line['contents'] . ' ' . $lines[$key + 1]['contents'];
                        }elseif(($index > $key &&! $index <= $key) && $nextOneExists){
                            $line = $lines[$index + 1];
                        }elseif(!$nextOneExists){
                            unset($lines[$index]);
                        }
                    }
                }
            }

            $cmdA = $lines[$i + 0];
            $cmdB = $lines[$i + 1]["contents"];
            $cmdC = $lines[$i + 2];
            $cmdD = $lines[$i + 3]["contents"];

            $ewe = "";
            if($cmdA["isstring"]){
                $ewe .= "\"" . $cmdA["contents"] . "\"";
            }else{
                $ewe .= "" . $cmdA["contents"] . "";
            }
            $ewe .= " ";
            switch($cmdB){
                case "equals":
                    $ewe .= "==";
                    break;
                case "greater than":
                    $ewe .= ">";
                    break;
                case "less than":
                    $ewe .= "<";
                    break;
                default:
                    $this->report_error("Invalid operator: $cmdB ");
                    break;
            }
            $ewe .= " ";
            $ewe .= "";
            if($cmdC["isstring"]){
                $ewe .= "\"" . $cmdC["contents"] . "\"";
            }else{
                $ewe .= "" . $cmdC["contents"] . "";
            }
            array_push($commands,$ewe);
            $i += 3;
            if($cmdD=="then"){
                $isand = 1;
                break;
            }else if($cmdD=="else"){
                $isand = 2;
                break;
            }
        }
        $result .= "if(";
        if($isand==2){
            $result .= "!(";
        }
        $result .= implode(" AND ",$commands);
        if($isand==2){
            $result .= ")";
        }
        $result .= ")";
        $result .= "{\n";
        $result .= $this->xantase_builder_line(array_slice($lines,$i + 1));
        $result .= "\n}";

        return $result;
    }

    private function xantase_builder_line(Array $lines): String{
        $datset = "";
        $prima = $lines[0];
        if($prima["isstring"]){
            $this->report_error("First token in a line cannot be a string");
        }
        switch($prima["contents"]){
            case "function":
                // create a function....
                $datset .= $this->xantase_builder_gen_func($lines);
                break;
            case "create":
                // create a variable....
                $datset .= $this->xantase_builder_gen_create($lines);
                break;
            case "set":
                // set a variable....
                $datset .= $this->xantase_builder_gen_set($lines,null);
                break;
            case "call":
                // call a function
                $datset .= $this->xantase_builder_gen_call($lines);
                break;
            case "spawn":
                // spawn a template
                $datset .= $this->xantase_builder_gen_spawn($lines);
                break;
            case "foreach":
                $datset .= $this->xantase_builder_gen_foreach($lines);
                break;
            case "end":
                $datset .= "}";
                break;
            case "remark":
                break;
            case "js":
                $datset .= $this->xantase_builder_gen_js($lines);
                break;
            case "if":
                $datset .= $this->xantase_builder_gen_if($lines);
                break;
            default:
                $this->report_error("Unknown token: " . $prima["contents"]);
                break;
        }
        return $datset;
    }

    /**
     * Creates the classcode for the created String
     * @param String $command_string the string we need to interpetate
     * @param String $classname the name of the class we going to make
     * @return String the interpetated file
     * @throws XantaseException when there is a error in the code 
     */
    public function xantase_interpetate_string(String $command_string,String $classname): String{
        $this->classname = $classname;
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
        foreach($lineset as $linenumber => $lines){
            if(empty($lines)){
                continue;
            }
            $this->linenumber = $linenumber;
            $datset .= "// " . $linebuffer[$linenumber] . "\n";
            $datset .= $this->xantase_builder_line($lines);
            $datset .= "\n";
        }

        if(!$this->hasbuild){
            throw new XantaseException("$classname has no build function!!");
        }

        $result = "class $classname extends XantaseBuildable{\nbase = null;\n" . $datset . "\n}";
        return $result;
    } 
}

?>