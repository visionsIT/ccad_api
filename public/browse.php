<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browsing Files</title>
    <style>
        div#images_box {
            display: flex;
            display: -ms-flexbox;
            flex-wrap: wrap;
        }

        div#images_box .dynamicImages {
            max-width: 180px;
            margin: 5px;
            width: 100%;
        }

        div#images_box .img_wrap {
            position: relative;
            overflow: hidden;
            padding-bottom: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        div#images_box .dynamicImages img {
            max-width: 100%;
            height: auto;
            position: absolute;
            left: 0;
            top: 0;
        }
        div#images_box .dynamicImages .url_img {
            width: 100%;
            overflow: hidden;
            word-break: break-all;
            font-size: 14px;
            position: relative;
            cursor: pointer;
        }
        div#images_box .dynamicImages .url_img span.btn_copy {
            background-color: rgba(0,0,0,0.7);
            color: #fff;
            width: 100%;
            height: 100%;
            text-align: center;
            display: flex;
            display: -ms-flexbox;
            align-items: center;
            justify-content: center;
            position: absolute;
            z-index: 1;
            left: 0;
            top: 0;
            font-weight: bold;
            font-size: 16px;
            opacity: 0;
        }
        div#images_box .dynamicImages .url_img:hover span.btn_copy {
            opacity: 1;
        }
    </style>
    <script>
        // browser/browse
        function loadDoc() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    var files = response.files;
                    var imgDiv = '';
                    for (let index = 0; index < files.length; index++) {
                        var element = files[index];
                        imgDiv += '<div class="dynamicImages"><div class="img_wrap" style="background-image: url('+ element +')"></div><p class="url_img"><span id="link_copy_' + index +'">' + element + '</span><span onclick="copyURL('+ index +')" class="btn_copy" id="copy_btn_'+ index +'">Copy Url</span></p></div>';
                    }
                    document.getElementById("images_box").innerHTML = imgDiv;
                }
            };
            xhttp.open("GET", "https://ccadapi.meritincentives.com/api/browser/browse", true);
            xhttp.send();
        }
        function copyURL(index) {
            var textToCopy = document.getElementById("link_copy_"+index).innerText;
            var myTemporaryInputElement = document.createElement("input");
            myTemporaryInputElement.type = "text";
            myTemporaryInputElement.value = textToCopy;
            document.body.appendChild(myTemporaryInputElement);
            myTemporaryInputElement.select();
            document.execCommand("Copy");
            document.body.removeChild(myTemporaryInputElement);
            document.getElementById("copy_btn_"+index).textContent="Copied!";
            setTimeout(function(){ 
                document.getElementById("copy_btn_"+index).textContent="Copy Url";
            }, 1000);
        }
    </script>
</head>
<body onload="loadDoc()">
    <div id="image_wrapper">
        <div id="images_box"></div>
    </div>
</body>
</html>