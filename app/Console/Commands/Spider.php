<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Baby;
use DiDom\Document;
use DiDom\Query;

class Spider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baby:spider {page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get baby information';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page = $this->argument('page');
        for ($i=1; $i <= $page; $i++) { 
            $url = 'http://www.weike27.com/class.asp?page='.$i.'&typeid=4&areaid=23'; 
            echo $url."\n";
            $this->listPage($url);
        }
        echo "$page";
    }

    function listPage($pageUrl)
    { 
        $document = new Document($pageUrl, true);

        $titles = $document->find('//div[@class="main dq1"]/ul[@class="list"]/li[@class="dq7 wd4"]/a', Query::TYPE_XPATH);
        foreach ($titles as $title) {
        $baseUrl = "http://www.weike27.com";

        $href = $title->getAttribute('href');
        if (strpos($href, "show.asp") !== false) {
            echo $title->text()."\n";
            // echo $href."\n";
            echo ">>>>>>>>>>\n";

            $detailPage = new Document($baseUrl.$href, true);

            $member_id = substr($href, strpos($href, "id=")+3, 5);
            $baby = Baby::create(['member_id' => $member_id]);
            $baby->fill(['title' => $title->text()]);

            $phone = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"联系方式")]');
            $connection = preg_replace('#\s+#','',$phone[0]->text());
            $baby->fill(['connection' => $connection]);

            $date = $detailPage->xpath('//div[@class="guize1"]/font[contains(text(), "发布时间")]');
            $date = substr($date[0]->text(), strpos($date[0]->text(), "发布时间")+15);
            $baby->fill(['public_date' => $date]);

            $area = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"所属地区")]');
            $area = preg_replace('#\s+#','',$area[0]->text());
            //todo 继续填数据

            $age = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"小姐年龄")]');
            $age = preg_replace('#\s+#','',$age[0]->text());

            $project = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"服务项目")]');
            $project = preg_replace('#\s+#','',$project[0]->text());

            $price = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"价格一览")]');
            $price = preg_replace('#\s+#','',$price[0]->text());

            $security = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"安全评估")]');
            $security = preg_replace('#\s+#','',$security[0]->text());

            $judge = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"综合评价")]');
            $judge = preg_replace('#\s+#','',$judge[0]->text());

            $detail = $detailPage->xpath('//div[@class="guize2"]/li[@class="neirong3"]');
            $detail = preg_replace('#\s+#','',$detail[0]->text());

            $images = $detailPage->xpath('//div[@class="guize2"]/div/a/img');
            $array = [];
            foreach ($images as $image) {
                $array[] = $baseUrl.$image->getAttribute('src');
            }
            $images = json_encode($array);

            // if (!checkExist($member_id)) {
            //   update($member_id, $title->text(), $connection, $date, $area, $age, $project, $price, $security, $judge, $detail, $images);
            // }
        }
    }
}

}
