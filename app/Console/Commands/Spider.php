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
        $province_count = 31;
        for ($province_id=1; $province_id <= $province_count; $province_id++) { 
            $page = $this->argument('page');
            for ($i=1; $i <= $page; $i++) { 
                $url = 'http://www.weike27.com/class.asp?page='.$i.'&typeid=4&areaid='.$province_id; 
                echo $url."\n";
                $this->listPage($url);
            }
        }
        
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
                echo ">>>>>>>>>>\n";

                $member_id = substr($href, strpos($href, "id=")+3, 5);
                //去除重复数据
                if (!$this->checkNotExist($member_id)) {
                    continue;
                }
                
                $detailPage = new Document($baseUrl.$href, true);

                $baby = new Baby();
                $baby->member_id = $member_id;
                $baby->title = $title->text();

                $phone = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"联系方式")]');
                $connection = preg_replace('#\s+#','',$phone[0]->text());
                $baby->connection = $connection;

                $date = $detailPage->xpath('//div[@class="guize1"]/font[contains(text(), "发布时间")]');
                $date = substr($date[0]->text(), strpos($date[0]->text(), "发布时间")+15);
                $baby->public_date = $date;

                $longArea = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"所属地区")]');
                $longArea = preg_replace('#\s+#','',$longArea[0]->text());
                $start = strlen("所属地区：");
                $index = strpos($longArea, " - ");
                //省份
                $province = substr($longArea, $start, $index-$start);
                $baby->province = $province;
                //地区
                $area = substr($longArea, $index+strlen(" - "));
                $baby->area = $area;

                $address = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"详细地址")]');
                $address = preg_replace('#\s+#','',$address[0]->text());
                $baby->address = $address;

                $age = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"小姐年龄")]');
                $age = preg_replace('#\s+#','',$age[0]->text());
                $baby->age = $age;

                $project = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"服务项目")]');
                $project = preg_replace('#\s+#','',$project[0]->text());
                $baby->project = $project;

                $price = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"价格一览")]');
                $price = preg_replace('#\s+#','',$price[0]->text());
                $baby->price = $price;

                $security = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"安全评估")]');
                $security = preg_replace('#\s+#','',$security[0]->text());
                $baby->security = $security;

                $judge = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"综合评价")]');
                $judge = preg_replace('#\s+#','',$judge[0]->text());
                $baby->judge = $judge;

                $detail = $detailPage->xpath('//div[@class="guize2"]/li[@class="neirong3"]');
                $detail = preg_replace('#^\s*||\s*$#', '', $detail[0]->text());
                $baby->detail = $detail;

                $images = $detailPage->xpath('//div[@class="guize2"]/div/a/img');
                $array = [];
                foreach ($images as $image) {
                    $array[] = $baseUrl.$image->getAttribute('src');
                }
                $images = json_encode($array);
                $baby->images = $images;

                try {
                    $baby->save();
                } catch (Exception $e) {
                    echo $e;
                    continue;
                }
            }
        }
    }

    function checkNotExist($member_id)
    {
        return Baby::where('member_id', $member_id)->get()->isEmpty();
    }

}
