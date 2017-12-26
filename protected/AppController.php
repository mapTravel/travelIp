<?php

use Routing\Router;
use Routing\Request;

class AppController
{
    /**
     * @var \Routing\Router
     */
    protected $router;
    protected $request;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->request = new Request();
    }

    public function mapAction()
    {
        echo self::render('map',array('rez' => '','keywords'=> ''));
    }
	public function user_importAction(){
		$files = $this->getFiles();
		echo self::render('user_import',array('files' => $files));
	}
	private function getFiles(){
		$files = array();
		$dir = scandir(CSV, 1);

		foreach($dir as $f){
			$newF = CSV.DIRECTORY_SEPARATOR.$f;
			$info = new SplFileInfo($newF);

			if($info->isFile() && $info->getExtension() == 'csv'){
				$pos = strripos($info->getFilename(), 'ok');
				$files[] = array(
					'filename' => $info->getFilename(),
					'time' => date("Y-m-d H:i:s",$info->getCTime()),
					'status' => ($pos === false) ? 'No' : 'Ok'
				);
			}
		}

		return $files;
	}
	public function ebayActionPost(){
		$rez = '';
		$keywords = '';
		if(IS_POST() && !empty($_POST['keywords'])){
			$keywords = $_POST['keywords'];
			$service = new FindingService([
				'credentials' => new Credent(),
				'globalId'    => GlobalIds::US,
				'sandbox' => true
			]);
			$request = new Types\FindItemsByKeywordsRequest();
			$request->keywords = $keywords;

			$respon = $service->findItemsByKeywords($request);

			$searchResult = $respon['respons']->searchResult[0]->item;
			if($respon['http_code'] == 200 && $respon['respons']->ack[0] == 'Success' && is_array($searchResult) && !empty($searchResult)){
				$rez = $searchResult;
			}else{
				$rez = 'could not find value';
			}
		}
		echo self::render('map',array('rez' => $rez,'keywords'=> $keywords));
	}


    public function userAction($id)
    {
        echo self::render('user', array('id' => $id));
    }

    public function error404Action()
    {
        header("HTTP/1.0 404 Not Found");
        echo self::render('error404');
    }

    protected function request($data)
    {
        echo $data;
    }

    protected function render($template, array $vars = array())
    {
        $root = __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;

        $templatePath = $root . $template . '.php';

        if (!is_file($templatePath)) {
            throw new \InvalidArgumentException(sprintf('Template "%s" not found in "%s"', $template, $templatePath));
        }

        extract($vars);

        $router = $this->router;
        $request = $this->request;

        ob_start();
        ob_implicit_flush(0);

        try {
            require($templatePath);
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }
}