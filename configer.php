<?
	

class Configer {
	var $file_name="";
	var $date=array();
	var $atribute;
	
	public function getAtributes() {
		$conf_str=file_get_contents($this->file_name);
		foreach($this->date as $n=>$cf) {
			if(preg_match("#".$n."[^\r\n]*//([^\n\r]+)[\n\r]#",$conf_str,$m))
				$this->atribute[$n]=trim($m[1]);
		}
	}
	
	public function safe(){
					
		$conf_str=var_export($this->date,true);

		//$atr=$model->getAtribute();
		foreach($this->atribute as $n=>$v) {
			$conf_str=preg_replace("#([^\n\r]*".$n."[^\n\r]*)[\n\r]#is","\\1//".$v."\n",$conf_str);
		}

		file_put_contents($this->file_name,"<? \n return ".$conf_str." \n ?>");

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
		
<style>
	#configForm span{display:inline-block; width:150px; }
	
</style>
<script>	
function addOption(t,name){

	var optList=$(t.parentNode).find('div');
	var opt=parseInt($(optList[optList.length-1]).find('span').html());
	
	$("<div><span> "+(opt+1)+" </span> <input type='text' name='config["+name+"]["+(opt+1)+"]'  value='' /> <a class='icon-remove' href='javascript:void(0)' onclick='removeOption(this)' ></a></div>").insertBefore(t);

}
function removeOption(t){
	$(t.parentNode).find('input').attr('value','');
	$(t.parentNode).css('display','none');
}
</script>

<h1>Настройки</h1>

<form class="form" id='configForm' method='post'>
<? 
foreach($this->atribute as $n=>$m) {
	
	if(is_array($this->date[$n])) {
		echo ("<fieldset><legend>".$m."</legend>");

		foreach($this->date[$n] as $nc=>$vc){
			
			if (strpos($m, "[static]") !== false) {
				$m = str_replace("[static]", "", $m);
				$static = true;
			} else {
				$static = false;
			}

			echo "<div ><span > ".$nc." </span> <input type='text' name='config[".$n."][".$nc."]'  value='".$vc."' />";
			if(!$static) {
				echo "<a class='icon-remove' href='javascript:void(0)' onclick='removeOption(this)' > del </a>";
			}
			echo "</div>";
		}
		if(!$static) {
			echo "<a class='icon-plus' href='javascript:void(0)' onclick='addOption(this,\"".$n."\")' > Добавить опцию</a>";
		}
		echo "</fieldset>";
	} else {
	echo "<div><span> ".$m." </span> <input type='text' name='config[".$n."]'  value='".$this->date[$n]."' />";
	if(!$static) {
		echo "<a class='icon-remove' href='javascript:void(0)' onclick='removeOption(this)' > del </a>";
	}
	echo "</div>";
	}
}

?>
<input type='submit' class='btn btn-info' value='Сохранить' />

</form><!-- form -->
		
	<? }
	
}

	
?>