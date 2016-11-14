<?
	

class Configer {
	var $file_name="";
	var $date=array();
	var $atribute;
	var $optForSelect = 4;
	var $booleanValues = array("0",0,"false",false,"1",1,"true",true);
	
	public function getAtributes() {
		$conf_str=file_get_contents($this->file_name);
		foreach($this->date as $n=>$cf) {
			if(preg_match("#".$n."[^\r\n]*//([^\n\r]+)[\n\r]#",$conf_str,$m)) {
				$this->atribute[$n]=trim($m[1]);
			}
			if(is_array($cf)) {
				//var_dump($cf);
				foreach($cf as $ns => $vs) {
					//echo "#".$n.".*".$ns."[^\r\n]*//([^\n\r]+)[\n\r]#";
					if(preg_match("#".$n.".*".$ns."[^\r\n]*=>[^\r\n]*//([^\n\r]+)[\n\r]#isU",$conf_str,$m)){
						$this->atribute[$n."-".$ns]=trim($m[1]);
					}
				}
			}
		}
	}
	
	public function readAtributes(){
		$atr = array();

	}
	
	public function safe(){
					
		$conf_str=var_export($this->date,true);

		//$atr=$model->getAtribute();
		foreach($this->date as $n=>$v) {
			if(isset($this->atribute[$n])) {
				$conf_str = preg_replace("#([^\n\r]*".$n."[^\n\r]*)[\n\r]#is","\\1//".$this->atribute[$n]."\n",$conf_str);
			}
			foreach($v as $ns => $vs){
				$conf_str = preg_match("#([^\n\r]*".$n.".*".$ns."[^\r\n]*=>[^\r\n]*)[\n\r]#isU","\\1//".$this->atribute[$n][$ns]."\n",$conf_str);
			}
		}

		file_put_contents($this->file_name,"<? \n return ".$conf_str." \n ?>");

	}
	
	public function renderInput( $title, $name_parent, $name_var=false, $data_var=false, $options=false ){
		$inputStr = "";
		
		// var_dump( $options ); 
		
		if(!$options or !in_array($data_var, $options['data'])) {
			$inputStr = "<span> ".$title." </span><input type='text' name='config[".$name_parent."]".($name_var?"[".$name_var."]":"")."'  value='".$data_var."' />";
		} else {
			if($options['type']==="checkbox" ) {
				if(in_array( $data_var, $this->booleanValues)) {
					$inputStr = "<input type='checkbox' ".($data_var?"checked='checked'":"")." name='config[".$name_parent."]".($name_var?"[".$name_var."]":"")."'  /> <span> ".$title." </span>";
				} else {
					$inputStr = "<span> ".$title." </span><input type='text' name='config[".$name_parent."]".($name_var?"[".$name_var."]":"")."'  value='".$data_var."' />";
				}
			}
			if($options['type']==="radio"){
				foreach($options['data'] as $n=>$v){
					$inputStr .= "<input type='radio' value='".$v."' ".($data_var===$v?"checked='checked'":"")." name='config[".$name_parent."]".($name_var?"[".$name_var."]":"")."'  /> <span> ".( isset($options['labels'][$n])?$options['labels'][$n]:$v )." </span>";
				}
				$inputStr = "<span> ".$title." </span>".$inputStr;
			}
			if($options['type']==="select"){
				foreach($options['data'] as $n=>$v){
					$inputStr .= "<option value='".$v."' ".($data_var===$v?"selected='selected'":"")." >  ".( isset($options['labels'][$n])?$options['labels'][$n]:$v )." </option> ";
				}
				$inputStr = "<span> ".$title." </span> <select name='config[".$name_parent."]".($name_var?"[".$name_var."]":"")."' > ".$inputStr."</select>";
			}
		}
		
		return $inputStr;
	}
	
	public function parseAtribute($atribute){
		$flags = array();
		
		
		$flags['title'] = $atribute;
		
		if (strpos($atribute, "[static]") !== false) {
			$flags['static'] = true;
		} else {
			$flags['static'] = false;
		}
		
		if (strpos($atribute, "[dinamic]") !== false) {
			$flags['dinamic'] = true;
		} 
		
		if (strpos($atribute, "[options") !== false) {
			preg_match("#\[options\|(.+)+\]#",$atribute,$options);

			$options  = explode ("|", $options[1]);

			if(sizeof($options)==2){
				$checkbox = true;
				foreach($options as $od){
					if(!in_array($od, $this->booleanValues, true) ){
						$checkbox = false;
					}
				}
				if($checkbox){
					$flags['options']['type']="checkbox";
				} else {
					$flags['options']['type']="radio";
				}
			} elseif(sizeof($options) < $this->optForSelect) {
				$flags['options']['type']="radio";
			} else {
				$flags['options']['type']="select";
			}
			
			$flags['options']['data'] = $options;
			
			foreach($options as $n=>$v){
				if(preg_match("#(.*)\((.*)\)#",$v,$m)) {
					$flags['options']['data'][$n] = trim($m[1]);
					$flags['options']['labels'][$n] = $m[2];
				}
			}

			
		} else {
			$flags['options'] = false;
		}
		
		$flags['title'] = trim(preg_replace("#\[[^\[\]]*\]#", "", $flags['title']));
		
		//var_dump( $flags['title'] );
		
		return $flags;
	}
	
	public function __construct($file_name){
		if(!is_file($file_name))
			return false;
		
		$this->file_name=$file_name;
		$this->date = require $this->file_name;
		
		$this->getAtributes();

		if($_SERVER['REQUEST_METHOD']==="POST") {
			
			if(isset($_POST['config']))
			{
		
				foreach($_POST['config'] as $n=>$v) {
					if(is_array($v))
					foreach($v as $n1=>$v1){
						if(trim($v1)===""){
							unset($_POST['config'][$n][$n1]);
						}
					}
					
					//if(sizeof($this->date[$n])>=sizeof($_POST['config'][$n]))
					$this->date[$n]=$_POST['config'][$n];
					
				}
				
				$this->safe();
			}
			
		}
	}
	

	function showForm(){ ?>
		
<script>
if(!window.jQuery){

document.write(unescape('<script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js">%3C/script%3E'));

}

</script>


<script>	

function addOption(t,name){

	var optList=$(t.parentNode).find('div');
	var opt=parseInt($(optList[optList.length-1]).find('span').html());
	
	$("<div><span> "+(opt+1)+" </span><input type='text' name='config["+name+"]["+(opt+1)+"]'  value='' /><a class='icon-remove' href='javascript:void(0)' onclick='removeOption(this)' >Del</a></div>").insertBefore(t);

}
function removeOption(t){
	$(t.parentNode).find('input').attr('value','');
	$(t.parentNode).remove();
}
</script>

<form class="form" id='configForm' method='post'>
<? 

//var_dump( $this->atribute );


foreach( $this->date as $n => $d_val ) {
	if( isset($this->atribute[$n]) ) {
		
		$flags['dinamic'] = false;
		$flags = $this->parseAtribute($this->atribute[$n]);
		
		/*
		echo "dinamic1 ";
		
		var_dump($flags['dinamic'], $flags['static']);
		*/
		
		if(is_array($d_val)) {
			echo ("<fieldset><legend>".$flags['title']."</legend>");

			foreach($d_val as $nc=>$vc){
				
				if( isset($this->atribute[$n."-".$nc]) ) { 
					$flags = array_merge( $flags, $this->parseAtribute($this->atribute[$n."-".$nc]) ) ;
				} else {
					$flags['title']=$nc;
				}
				
				echo "<div>".$this->renderInput($flags['title'], $n, $nc, $vc, $flags['options'] );

				//echo "<div ><span > ".(isset($this->atribute[$n."-".$nc])?$flags['title']:$nc)." </span> <input type='text' name='config[".$n."][".$nc."]'  value='".$vc."' />";
				
				/*
				echo "dinamic ";
				var_dump($flags['dinamic'], $flags['static']);
				*/
				
				if(isset($flags['dinamic']) and $flags['dinamic'] and !$flags['static']) {
					echo "<a class='icon-remove' href='javascript:void(0)' onclick='removeOption(this)' >Del</a>";
				}
				echo "</div>";
				
				if(isset($flags['dinamic']) and $flags['dinamic']){
					$flags['static']=false;
				}
			}
			if(isset($flags['dinamic']) and $flags['dinamic'] and !$flags['static']) {
				echo "<a class='icon-plus' href='javascript:void(0)' onclick='addOption(this,\"".$n."\")' >Add</a>";
			}
			echo "</fieldset>";
		} else {
			echo "<div>";
			echo $this->renderInput($flags['title'], $n, false, $d_val, $flags['options'] );
			echo "</div>";
		}
}
}

?>
<input type='submit' class='btn btn-info' value='Save' />

</form><!-- form -->
		
	<? }
	
}

	
?>