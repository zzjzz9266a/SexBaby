<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Baby;
use DiDom\Document;
use DiDom\Query;

class Detail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baby:detail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get baby information which not specific';

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
        $totalCount = 0;
        $successCount = 0;
        $babies = Baby::orderBy('public_date', 'desc')->get();
        foreach ($babies as $baby) {
            if (str_contains($baby->connection, '查看内容')) {
                $totalCount += 1;
                echo $baby->member_id."-----".$baby->title."\n";
                $result = $this->updateSingle($baby);
                echo $result ? "更新成功"."\n" : "失败"."\n";
                if ($result) {
                    $successCount += 1;
                }
            }
        }
        echo "本次共有".$totalCount."条数据，成功更新".$successCount;
    }

    public function updateSingle(Baby $baby)
    {
        $baseUrl = "http://www.weike27.com/";
        $detailPage = new Document($baseUrl."show.asp?id=".$baby->member_id, true);
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
        }
        //获取联系方式即为成功
        $result = !str_contains($connection, '查看内容');
        return $result;
    }
}