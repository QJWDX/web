<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        *{
            margin:0;
            padding:0;
        }
        body,html{
            background-color: #EEF2F6;
            width:100%;
            height:100%;
        }
        main{
            display: flex;
            align-items: center;
            justify-content: center;
            height:100%;
            flex-direction: column;
        }
        .wrap_all{
            width:50%;
            height:67%;
            background-color: #fff;
            box-shadow: 0px 0px 8px 2px rgba(51, 51, 51, 0.2);
        }
        .wrap_log{
            margin-bottom:32px;
        }
        .share_head{
            background-color: #3D90DD;
            color:#fff;
            font-size:18px;
            height:54px;
            line-height: 54px;
            text-align: center;
        }
        .download_tips{
            color:#222222;
            font-size:20px;
            padding:20px 0 20px 20px;
            font-weight: bold;
            border-bottom:1px solid #e4e5e7;
        }
        .list_item{
            display: flex;
            align-items: center;
            padding:10px 50px;
            color:#222222;
        }
        .list_item.active{
            border-top:1px solid #e7eef8;
            border-bottom:1px solid #e7eef8;
            background-color: #edf6ff;
        }
        .list_item>img{
            margin-right:15px;
        }
        .wrap_links{
            flex:1;
        }
        .list_item .download{
            color:#4AA8FF;
            cursor: pointer;
        }
        .download_list{
            height:calc(100% - 160px);
        }
        .download_warn{
            color: #E52828;
            padding-left:20px;
        }
    </style>
</head>
<body>
    <main>
        @php
            $data = [];
            foreach ($shareContent['files'] as $file){
                $data[] = [
                    "name" => trim($file['name']),
                    "path" => trim($file['path'])
                ];
            }
        @endphp
        <div class="wrap_log"><img src="/assets/img/logo.png" alt=""></div>
        <div class="wrap_all">
            <div class="share_head">文件分享</div>
            <div class="download_tips">{{ $shareContent['title'] }}</div>
            <div class="download_list"></div>
            <div class="download_warn">*备注：该链接仅打开一次有效；请不要重新刷新页面，重新刷新后页面失效；</div>
        </div>
    </main>
    <script src="/assets/js/jquery-3.5.1.min.js"></script>
    <script>
        $(function () {
            var data = JSON.parse('{!! json_encode($data) !!}');
            var temp = '';
            var fileIcon = {
                'doc':'../../assets/img/doc.png',
                'docx':'../../assets/img/doc.png',
                'xlsx':'../../assets/img/excel.png',
                'xls':'../../assets/img/excel.png',
                'jpg':'../../assets/img/jpg.png',
                'pdf':'../../assets/img/pdf.png',
                'txt':'../../assets/img/txt.png'
            };
            data.forEach(item=>{
                var arr = item.path.split('.').reverse()
                temp += '<div class="list_item">'+
                    '<img src="'+fileIcon[arr[0]]+'" alt="">'+
                    '<div class="wrap_links">'+item.name+'</div>'+
                    '<div class="download" link="'+item.path+'" fileName="'+item.name+'" fileType="'+fileIcon[arr[0]]+'">下载</div></div>'
            })
            $('.download_list').html(temp)
            $('.download_list').on('click','.download',function(){
                var a = document.createElement("a");
                a.target = '_blank';
                a.href = $(this).attr('link'); //文件地址
                a.download = $(this).attr('fileName'); //文件名及格式
                document.body.appendChild(a);
                a.click();
                $(this).text('重新下载')
            })
        });
    </script>
</body>
</html>
