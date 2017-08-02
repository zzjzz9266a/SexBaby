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
        $babies = Baby::all();
        foreach ($babies as $baby) {
            if (str_contains($baby->connection, '查看内容')) {
                $totalCount += 1;
                echo $baby->member_id."-----".$baby->title."\n";
                $result = $this->updateSingle($baby, false);
                if (!$result) {
                    $result = $this->updateSingle($baby, true);
                }
                echo $result ? "更新成功"."\n" : "更新失败"."\n";
                if ($result) {
                    $successCount += 1;
                }
            }
        }
        echo "本次共有".$totalCount."条数据，成功更新".$successCount;
    }

    public function updateSingle(Baby $baby, $hasHeader)
    {
        $baseUrl = "http://www.weike27.com/";
        if ($hasHeader) {
            $detailPage = new Document($baseUrl."show.asp?id=".$baby->member_id, true, 'GBK', 'html', $this->header());
        }else{
            $detailPage = new Document($baseUrl."show.asp?id=".$baby->member_id, true, 'GBK', 'html');//, $this->header());
        }
        if ($detailPage->getResponseCode() != 200) {
            echo "请求失败，状态码：".$detailPage->getResponseCode()."\n";
            return false;
        }

        $phone = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"联系方式")]');
        if ($phone) {
            $connection = preg_replace('#\s+#','',$phone[0]->text());
            $baby->connection = $connection;
        }else{
            return false;
        }

        $date = $detailPage->xpath('//div[@class="guize1"]/font[contains(text(), "发布时间")]');
        if ($date) {
            $date = substr($date[0]->text(), strpos($date[0]->text(), "发布时间")+15);
            $baby->public_date = $date;
        }

        $longArea = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"所属地区")]');
        if ($longArea) {
            $longArea = preg_replace('#\s+#','',$longArea[0]->text());
            $start = strlen("所属地区：");
            $index = strpos($longArea, " - ");
            //省份
            $province = substr($longArea, $start, $index-$start);
            $baby->province = $province;
            //地区
            $area = substr($longArea, $index+strlen(" - "));
            $baby->area = $area;
        }

        $address = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"详细地址")]');
        if ($address) {
            $address = preg_replace('#\s+#','',$address[0]->text());
            $baby->address = $address;
        }

        $age = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"小姐年龄")]');
        if ($age) {
            $age = preg_replace('#\s+#','',$age[0]->text());
            $baby->age = $age;
        }

        $project = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"服务项目")]');
        if ($project) {
            $project = preg_replace('#\s+#','',$project[0]->text());
            $baby->project = $project;
        }

        $price = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"价格一览")]');
        if ($price) {
            $price = preg_replace('#\s+#','',$price[0]->text());
            $baby->price = $price;
        }

        $security = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"安全评估")]');
        if ($security) {
            $security = preg_replace('#\s+#','',$security[0]->text());
            $baby->security = $security;
        }

        $judge = $detailPage->xpath('//div[@class="guize2"]/li[contains(text(),"综合评价")]');
        if ($judge) {
            $judge = preg_replace('#\s+#','',$judge[0]->text());
            $baby->judge = $judge;
        }

        $detail = $detailPage->xpath('//div[@class="guize2"]/li[@class="neirong3"]');
        if ($detail) {
            $detail = preg_replace('#^\s*||\s*$#', '', $detail[0]->text());
            $baby->detail = $detail;
        }

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

    public function header()
    {
        $header = array();
        $header[] = 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
        $header[] = 'Accept-Encoding:gzip, deflate';
        $header[] = 'Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4';
        $header[] = 'Connection:keep-alive';
        // $header[] = 'Cookie:ASPSESSIONIDSQRSDBAS=LLAGADAAHFBFDKEPMMENIIKK; ASPSESSIONIDQQTRBDBS=IJJPPPMDLHDDMGDHOBMNPFDD; ASPSESSIONIDQQTRCABT=POCLOMJDPOCCMPJHDELAFNAK; ASPSESSIONIDQQTRDBBS=LEDLOMJDAPPCFKFAPBFOBNOJ; ASPSESSIONIDQSQTBAAT=GFMFBGDAKNHKLKCGKKHFBCBN; ASPSESSIONIDQSTSCAAT=PPGHCJGAHHFBMDLCNOPNECMN; usercookies%5F873983=dayarticlenum=0&daysoftnum=0&userip=121%2E69%2E48%2E156; NewAspUsers=RegDateTime=2017%2D07%2D27+00%3A19%3A45&UserToday=0%2C0%2C0%2C0%2C0%2C0&userlastip=121%2E69%2E48%2E156&UserGroup=%C6%D5%CD%A8%BB%E1%D4%B1&usermail=my%40email%2Ecom&UserLogin=22&UserGrade=1&password=4c9ea2b7ef321612&UserClass=0&username=zzjzz9266a&nickname=zzjzz9266a&usercookies=0&userid=873983&LastTime=2017%2D8%2D2+10%3A25%3A05&LastTimeIP=121%2E69%2E48%2E156&LastTimeDate=2017%2D8%2D2+10%3A25%3A05';
        $header[] = 'Cookie:ASPSESSIONIDSQRSDBAS=LLAGADAAHFBFDKEPMMENIIKK; ASPSESSIONIDQQTRBDBS=IJJPPPMDLHDDMGDHOBMNPFDD; ASPSESSIONIDQQTRCABT=POCLOMJDPOCCMPJHDELAFNAK; ASPSESSIONIDQQTRDBBS=LEDLOMJDAPPCFKFAPBFOBNOJ; ASPSESSIONIDQSQTBAAT=GFMFBGDAKNHKLKCGKKHFBCBN; ASPSESSIONIDQSTSCAAT=PPGHCJGAHHFBMDLCNOPNECMN; usercookies%5F873983=dayarticlenum=0&daysoftnum=0&userip=121%2E69%2E48%2E156; NewAspUsers=RegDateTime=2017%2D07%2D27+00%3A19%3A45&UserToday=0%2C0%2C0%2C0%2C0%2C0&userlastip=121%2E69%2E48%2E156&UserGroup=%C6%D5%CD%A8%BB%E1%D4%B1&usermail=my%40email%2Ecom&UserLogin=22&UserGrade=1&password=4c9ea2b7ef321612&UserClass=0&username=zzjzz9266a&nickname=zzjzz9266a&usercookies=0&userid=873983&LastTime=2017%2D8%2D2+10%3A25%3A05&LastTimeIP=121%2E69%2E48%2E156&LastTimeDate=2017%2D8%2D2+10%3A25%3A05; ASPSESSIONIDSSRSAAAS=PHKNDMJAAPNALCHOEFCBFHIJ';
        $header[] = 'Host:www.weike27.com';
        $header[] = 'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36';
        return $header;
    }
}