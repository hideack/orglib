<?php
/**
 * Naivebayes.php 
 *
 * This package was ported from Perl's Algorithm::NaiveBayes (frequency model only)
 * http://search.cpan.org/~kwilliams/Algorithm-NaiveBayes-0.04/lib/Algorithm/NaiveBayes.pm
 * 
 * @category  Algorithm 
 * @package   Naivebayes
 * @author    hideack <author@mail.com>
 * @license   http://www.php.net/license/3_01.txt The PHP License, version 3.01
 * @version   0.1 
 * @link       
 * @see       
 */
class Naivebayes{

  	private $modeltype;
	private $instances;
	private $trainingdata;
	private $model;

	public function __construct(){
		$this->trainingdata = array(
			"attributes" => array(),
			"labels"     => array(),
		);
		$this->instances = 0;
		$this->modeltype = "";	// Perl版では切り替え... 今回は切り替え無し
	}

    /**
     * カテゴライザに学習則を追加 
     * 
     * @param  array  $attributes 単語の出現回数 
     * @param  array  $label      学習則に与えられるラベル
     * @return void  
     * @access public
     */
	public function addInstance($attributes, $label){
		$this->instances++;

		foreach($attributes as $keyword => $count){
			if(isset($this->trainingdata['attributes'][$keyword])){
				$this->trainingdata['attributes'][$keyword] += $count;
			}
			else{
				$this->trainingdata['attributes'][$keyword] = $count;
			}
		}

		foreach($label as $labelword){
			if(isset($this->trainingdata['labels'][$labelword]['count'])){
				$this->trainingdata['labels'][$labelword]['count']++;
			}
			else{
				$this->trainingdata['labels'][$labelword]['count'] = 1;
			}
			
			foreach($attributes as $keyword => $count){
				if(isset($this->trainingdata[$keyword])){
					$this->trainingdata['labels'][$labelword]['attributes'][$keyword] += $count;
				}
				else{
					$this->trainingdata['labels'][$labelword]['attributes'][$keyword] = $count;
				}
			}
		}
	}

    /**
     * 与えられた学習則から確率を計算 
     * 
     * @return void  
     * @access public
     */
	public function train(){
		$m = array();
		$labels = $this->trainingdata['labels'];

		$m['attributes'] = $this->trainingdata['attributes'];
		$vocab_size = count($m['attributes']);

		foreach($labels as $label => $info){

			$m['prior_probs'][$label] = log($info['count'] / $this->instances);
			
			$label_tokens = 0;
			foreach($info['attributes'] as $word => $count){
				$label_tokens += $count;
			}
			
			$m['smoother'][$label] = -log($label_tokens + $vocab_size);
			$denominator = log($label_tokens + $vocab_size);

			foreach($info['attributes'] as $attribute => $count){
				$m['probs'][$label][$attribute] = log($count + 1) - $denominator;
			}
		}

		$this->model = $m;
	}

    /**
     * 未知のインスタンスからラベルの予測を実施
     * 
     * @param  array  $newattrs 単語の出現回数からラベルを推測
     * @return array  Return    推測されるラベルとその確率 
     * @access public
     */
	public function predict($newattrs){
		$scores = $this->model['prior_probs'];

		foreach($newattrs as $feature => $value){
			foreach($this->model['probs'] as $label => $attribute){
				$tmpscore = 0.0;
				
				if($attribute[$feature] == 0.0){
					$tmpscore = $this->model['smoother'][$label];
				}
				else{
					$tmpscore = $attribute[$feature];
				}

				$scores[$label] += $tmpscore * $value;
			}
		}

		$scores = $this->rescale($scores);

		return $scores;
	}

    /**
     * 既存のラベルの取得 
     * 
     * @return array  Return ラベル一覧 
     * @access public
     */
	public function labels(){
		$labels = array();
		
		foreach($this->trainingdata['labels'] as $label => $value){
			$labels[] = $label;
		}

		return $labels; 
	}

    /**
     * 学習則の初期化 (未実装)
     * 
     * @return void  
     * @access public
     */
	public function doPurge(){
	}

    /**
     * 正規化処理 
     * 
     * @param  array $scores 正規化対象配列 
     * @return array Return  正規化処理された配列 
     * @access private
     */
	private function rescale($scores){
		$total = 0;
		$max  = max($scores);
		$rescalescore = $scores;

		foreach($rescalescore as $key => $val){
			$val = exp($val - $max);
			$total += pow($val, 2);
			
			$rescalescore[$key] = $val;
		}

		$total = sqrt($total);

		foreach($rescalescore as $key => $val){
			$rescalescore[$key] /= $total; 
		}

		return $rescalescore;
	}
}

$bayes = new NaiveBayes();
$bayes->addInstance(array("はてな" => 5, "京都" => 2), array("it"));
$bayes->addInstance(array("引っ越し" => 1, "春" => 1), array("life"));
$bayes->train();
$resp = $bayes->predict(array("はてな" => 1, "引っ越し" => 1, "京都" => 1));

print_r($resp);

?>
