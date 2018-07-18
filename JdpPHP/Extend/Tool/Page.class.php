<?php
	class Page {
		private $total; //数据表中总记录数
		private $listRows; //每页显示行数
		private $limit;
		private $uri;
		private $pageNum; //页数
		private $config=array('header'=>"个记录", "prev"=>"上一页", "next"=>"下一页", "first"=>"首 页", "last"=>"尾 页");
		private $listNum=8;
		/*
		 * $total 
		 * $listRows
		 */
		public function __construct($total, $listRows=10, $pa=""){
			$this->total=$total;
			$this->listRows=$listRows;
			$this->uri=$this->getUri($pa);
			$this->page=!empty($_GET["page"]) ? $_GET["page"] : 1;
			$this->pageNum=ceil($this->total/$this->listRows);
			$this->limit=$this->setLimit();
		}

		private function setLimit(){
	
			return ($this->page-1)*$this->listRows.", {$this->listRows}";
		}

		private function getUri($pa){
			$url=$_SERVER["REQUEST_URI"].(strpos($_SERVER["REQUEST_URI"], '?')?'':"?").$pa;
			$parse=parse_url($url);

		

			if(isset($parse["query"])){
				parse_str($parse['query'],$params);
				unset($params["page"]);
				$url=$parse['path'].'?'.http_build_query($params);
				
			}

			return $url;
		}

		function __get($args){
			if($args=="limit")
				return $this->limit;
			else
				return null;
		}

		private function start(){
			if($this->total==0)
				return 0;
			else
				return ($this->page-1)*$this->listRows+1;
		}

		private function end(){
			return min($this->page*$this->listRows,$this->total);
		}

		private function first(){
            $html = "";
			if($this->page==1)
				$html.='';
			else
				$html.="<a href='{$this->uri}&page=1'>{$this->config["first"]}</a>";

			return $html;
		}

		private function prev(){
            $html = "";
			if($this->page==1)
				$html.='';
			else
				$html.="<a href='{$this->uri}&page=".($this->page-1)."'>{$this->config["prev"]}</a>";

			return $html;
		}

		private function pageList(){
			$linkPage="";
			
			$inum=floor($this->listNum/2);
		
			for($i=$inum; $i>=1; $i--){
				$page=$this->page-$i;

				if($page<1)
					continue;

				$linkPage.="<a href='{$this->uri}&page={$page}'>{$page}</a>";

			}
		
			$linkPage.="<a class='active'>{$this->page}</a>";
			

			for($i=1; $i<=$inum; $i++){
				$page=$this->page+$i;
				if($page<=$this->pageNum)
					$linkPage.="<a href='{$this->uri}&page={$page}'>{$page}</a>";
				else
					break;
			}

			return $linkPage;
		}

		private function next(){
            $html = "";
			if($this->page==$this->pageNum)
				$html.='';
			else
				$html.="<a href='{$this->uri}&page=".($this->page+1)."'>{$this->config["next"]}</a>";

			return $html;
		}

		private function last(){
            $html = "";
			if($this->page==$this->pageNum)
				$html.='';
			else
				$html.="<a href='{$this->uri}&page=".($this->pageNum)."'>{$this->config["last"]}</a>";

			return $html;
		}

		private function goPage(){
			return '<lable style="display:inline-block;">
			<input type="hidden" id="go_page_hidden" value="'.$this->page.'" style="width:40px">
			<input type="text" id="go_page"  onkeyup="getPageNum();" value="'.$this->page.'" style="width:40px">
			<input type="button" value="GO" class="go_page" onclick="javascript:var page=document.getElementById(\'go_page\').value; page=(page>'.$this->pageNum.')?'.$this->pageNum.':page;location=\''.$this->uri.'&page=\'+page+\'\'" ></lable>
			<script>
			function getPageNum(){
				var page=document.getElementById("go_page").value;				
				document.getElementById("go_page_hidden").value=\''.$this->uri.'&page=\'+page+\'\';
			}
			</script>';
		}
		function page($display=array(0,1,2,3,4,5,6,7,8)){
			$html[0]="&nbsp;&nbsp;共有<b>{$this->total}</b>{$this->config["header"]}&nbsp;&nbsp;";
			$html[1]="&nbsp;&nbsp;每页显示<b>".($this->end()-$this->start()+1)."</b>条，本页<b>{$this->start()}-{$this->end()}</b>条&nbsp;&nbsp;";
			$html[2]="&nbsp;&nbsp;<b>{$this->page}/{$this->pageNum}</b>页&nbsp;&nbsp;";
			
			$html[3]=$this->first();
			$html[4]=$this->prev();
			$html[5]=$this->pageList();
			$html[6]=$this->next();
			$html[7]=$this->last();
			$html[8]=$this->goPage();
			$page='';
			foreach($display as $index){
				$page.=$html[$index];
			}

			return $page;

		}

	
	}
		
		
