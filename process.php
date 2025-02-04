<?php
require_once "config.php";

$con = mysqli_connect(host, username, password, database);
// if ($_SERVER['SERVER_ADDR'] == $_SERVER["REMOTE_ADDR"]) {
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 找到对应ID
    $reqID = null;
    if (array_key_exists("tokenusername", $_REQUEST) && array_key_exists("tokenpassword", $_REQUEST)) {
        $reqID = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `users` WHERE `name` = '" . mysqli_escape_string($con, $_REQUEST["tokenusername"]) . "'"))["id"];
    }
    function createSharedRecord($id): void
    {
        global $con;
        do {
            $sharecode = "";
            for ($i = 0; $i < constant('share-code-A-part-length') + constant('share-code-B-part-length'); $i++) {
                $sharecode .= constant('code-resource')[mt_rand(0, 35)];
            }
        } while (mysqli_num_rows(mysqli_query($con, "SELECT * FROM `sharedinfo` WHERE `sharecode` = '" . $sharecode . "'")));
        mysqli_query($con, "INSERT INTO `sharedinfo` VALUES (" . $id . ",'" . $sharecode . "',0)");
    }
    function generateNewShareCode($id): void
    {
        global $con;
        $oldsharecode = substr(mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `sharedinfo` WHERE `fileid` = " . $id))["sharecode"], 0, constant('share-code-A-part-length'));
        do {
            $sharecode = $oldsharecode;
            for ($i = 0; $i < constant('share-code-B-part-length'); $i++) {
                $sharecode .= constant('code-resource')[mt_rand(0, 35)];
            }
        } while (mysqli_num_rows(mysqli_query($con, "SELECT * FROM `sharedinfo` WHERE `sharecode` = '" . $sharecode . "' AND `fileid` = " . $id)));
        mysqli_query($con, "UPDATE `sharedinfo` SET sharecode = '" . $sharecode . "' WHERE `fileid` = " . $id);
    }
    function maskString($str): string
    {
        return $str[0] . "***" . substr($str, -1);
    }
    function getRemoteIP(): string
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $client_ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $client_ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR')) {
            $client_ip = getenv('REMOTE_ADDR');
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $client_ip;
    }
    function getReadersList(): array
    {
        global $con, $reqID;
        $readerlist = [];
        foreach (mysqli_fetch_all(mysqli_query($con, "SELECT * FROM `readsharedfilelog` WHERE `userid` = " . $reqID . " AND `fileid` = " . $_REQUEST["fileid"]), MYSQLI_ASSOC) as $value) {
            array_push($readerlist, array_merge(mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `users` WHERE `id` = " . $value["userid"])), ["readtime" => $value["readtime"]]));
        }
        return $readerlist;
    }
    function randomToken()
    {
        $key = "";
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 16; $i++) {
            $key .= $pattern[mt_rand(0, strlen($pattern) - 1)];    //生成php随机数   
        }
        return $key;
    }
    // 获得请求token对应ID
    $token = randomToken();
    switch ($_REQUEST["type"]) {
        case "ping-back":
            echo "ping.receive";
            break;
        case "register":
            if (mysqli_num_rows(mysqli_query($con, "SELECT * FROM `users` WHERE `name` = '" . $_REQUEST["name"] . "'")) > 0) {
                echo "register.error.alreadyExists";
            } else {
                mysqli_query($con, "INSERT INTO `users` VALUES (NULL,'" . mysqli_escape_string($con, $_REQUEST["name"]) . "','" . $_REQUEST["passwordENC"] . "',CURRENT_TIMESTAMP(),'" . getRemoteIP() . "','" . randomToken() . "')");
                $addid = mysqli_insert_id($con);
                mysqli_query($con, "INSERT INTO `accessinfo` VALUES (" . $addid . "," . constant('default-file-length-limit') . "," . constant('default-files-count-limit') . ",1)");
                if ($_REQUEST["keeplogined"] == "true") {
                    setcookie("token", $token, time() + 7 * 24 * 3600);
                } else {
                    setcookie("token", $token, time() + 1 * 24 * 3600);
                }
                echo "register.success";
            }
            break;
        case "login":
            $res = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `users` WHERE `name` = '" . mysqli_escape_string($con, $_REQUEST["name"]) . "'"));
            if (is_null($res) || $res["password"] != $_REQUEST["passwordENC"]) {
                echo "login.error.doesNotExistOrPasswordWrong";
            } else {
                if ($_REQUEST["keeplogined"] == "true") {
                    setcookie("token", $token, time() + 7 * 24 * 3600);
                } else {
                    setcookie("token", $token, time() + 1 * 24 * 3600);
                }
                mysqli_query($con, "UPDATE `users` SET `lastloginIP` = '" . getRemoteIP() . "', `token` = '" . $token . "' WHERE `id` = " . $res["id"]);
                echo "login.success";
            }
            break;
        case "request-info":
            mysqli_query($con, "UPDATE `users` SET `lastonlinetime` = CURRENT_TIMESTAMP() WHERE `id` = " . $reqID);
            $queryArr = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `accessinfo` WHERE `id` = " . $reqID));
            $queryArr2 = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `users` WHERE `id` = " . $reqID));
            $resultArr = mysqli_fetch_all(mysqli_query($con, "SELECT `id`,`name`,`lastmodifiedtime`,length(CONVERT(DES_DECRYPT(`content`,'" . mysqli_escape_string($con, constant("encrypt-key")) . "') USING utf8mb4)) AS `filesize` FROM `files` WHERE ownerid = " . $reqID), MYSQLI_ASSOC);
            foreach ($resultArr as $key => $value) {
                $resultArr[$key] = array_merge($resultArr[$key], mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `sharedinfo` WHERE `fileid` = " . $value["id"])));
            }
            echo json_encode([
                "userinfo" => $queryArr2,
                "constants" => [
                    "fileNameLimitLength" => 20,
                    "shareCodeLength" =>  constant('share-code-A-part-length') + constant('share-code-B-part-length'),
                    "fileLengthLimit" => $queryArr["filelengthlimit"],
                    "filesCountLimit" => $queryArr["filescountlimit"],
                    "deleteFileAble" => $queryArr["deleteable"]
                ],
                "files" => $resultArr
            ]);
            break;
        case "change-password":
            if (mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `users` WHERE `name` = '" . mysqli_escape_string($con, $_REQUEST["name"]) . "'"))["password"] == $_REQUEST["oldpasswordENC"]) {
                mysqli_query($con, "UPDATE `users` SET `password` = '" . $_REQUEST["newpasswordENC"] . "' WHERE `id` = " . $reqID);
                echo "changePassword.success";
            } else {
                echo "changePassword.error.passwordNotSame";
            }
        case "delete-account":
            // COOKIE设置:已被移除
            mysqli_query($con, "DELETE FROM `files` WHERE `ownerid` = " . $reqID);
            mysqli_query($con, "DELETE FROM `readsharedfilelog` WHERE `userid` = " . $reqID);
            mysqli_query($con, "DELETE FROM `accessinfo` WHERE `id` = " . $reqID);
            mysqli_query($con, "DELETE FROM `users` WHERE `id` = " . $reqID);
            echo "deleteAccount.success";
            break;
        case "read-file":
            echo json_encode([
                "fileinfo" =>
                mysqli_fetch_assoc(mysqli_query(
                    $con,
                    "SELECT `id`,`name`,CONVERT(DES_DECRYPT(`content`,'" . mysqli_escape_string($con, constant("encrypt-key")) . "') USING utf8mb4) AS `content`,`lastmodifiedtime` FROM `files` WHERE id = " . $_REQUEST["fileid"]
                )),
                "shareinfo" =>
                mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `sharedinfo` WHERE `fileid` = " . $_REQUEST["fileid"])),
                "sharedfilereadinfo" => getReadersList()
            ]);
            break;
        case "save-file":
            function isDataDuplicate($content)
            {
                global $con;
                // 对要插入的数据进行转义，防止 SQL 注入
                // 查询数据库中是否存在相同的数据
                $result = mysqli_query($con, "SELECT COUNT(*) AS count FROM `files` WHERE `content` = DES_ENCRYPT('" . mysqli_escape_string($con, urldecode($content)) . "','" . constant('encrypt-key') . "')");
                $row = mysqli_fetch_assoc($result);
                return $row['count'];
            }
            if (!isDataDuplicate($_REQUEST["filecontent"])) {
                generateNewShareCode($_REQUEST["fileid"]);
            }
            mysqli_query($con, "UPDATE `files` SET `lastmodifiedtime` = CURRENT_TIMESTAMP() , `content` = DES_ENCRYPT('" . mysqli_escape_string($con, urldecode($_REQUEST["filecontent"])) . "','" . mysqli_escape_string($con, constant("encrypt-key")) . "') WHERE `id` = " . $_REQUEST["fileid"]);
            echo "savefile.success";
            break;
        case "rename-file":
            mysqli_query($con, "UPDATE `files` SET `name` = '" . mysqli_escape_string($con, urldecode($_REQUEST["newname"])) . "' WHERE `id` = " . $_REQUEST["fileid"]);
            echo "renameFile.success";
            break;
        case "create-file":
            mysqli_query($con, "INSERT INTO `files` VALUES (NULL,'" . mysqli_escape_string($con, urldecode($_REQUEST["filename"])) . "'," . $reqID . ",'',CURRENT_TIMESTAMP())");
            createSharedRecord(mysqli_insert_id($con));
            echo "createFile.success";
            break;
        case "delete-file":
            mysqli_query($con, "DELETE FROM `readsharedfilelog` WHERE `fileid` = " . $_REQUEST["fileid"]);
            mysqli_query($con, "DELETE FROM `files` WHERE `id` = " . $_REQUEST["fileid"]);
            echo "deleteFile.success";
            break;
        case "change-username":
            if (mysqli_num_rows(mysqli_query($con, "SELECT * FROM `users` WHERE `name` = '" . mysqli_escape_string($con, $_REQUEST["newusername"]) . "'")) > 0) {
                echo "changeUserName.error.alreadyExists";
            } else {
                mysqli_query($con, "UPDATE `users` SET `name` = '" . mysqli_escape_string($con, $_REQUEST["newusername"]) . "' WHERE id = " . $reqID);
                echo "changeUserName.success";
            }
            break;
        case "copy-file":
            mysqli_query($con, "INSERT INTO `files` SELECT NULL,'" . mysqli_escape_string($con, $_REQUEST["newname"]) . "',`ownerid`,`content`,CURRENT_TIMESTAMP() FROM `files` WHERE id = " . $_REQUEST["fileid"]);
            createSharedRecord(mysqli_insert_id($con));
            echo "copyFile.success";
            break;
        case "upload-file":
            mysqli_query($con, "INSERT INTO `files` VALUES (NULL,'" . mysqli_escape_string($con, urldecode($_REQUEST["filename"])) . "'," . $reqID . ",DES_ENCRYPT('" . mysqli_escape_string($con, urldecode($_REQUEST["filecontent"])) . "','" . mysqli_escape_string($con, constant("encrypt-key")) . "'),CURRENT_TIMESTAMP())");
            createSharedRecord(mysqli_insert_id($con));
            echo "uploadFile.success";
            break;
        case "change-shared-status":
            mysqli_query($con, "UPDATE `sharedinfo` SET avaliable = " . ($_REQUEST["method"] == "on" ? "1" : "0") . " WHERE fileid = " . $_REQUEST["fileid"]);
            echo "changeSharedStatus.success";
            break;
        case "open-shared-file":
            if (mysqli_num_rows(mysqli_query($con, "SELECT * FROM `sharedinfo` WHERE `sharecode` = '" . mysqli_escape_string($con, strtoupper($_REQUEST["sharecode"])) . "'")) == 0) {
                echo json_encode([
                    "status" => "error.shareCodeErrorOrShareIsClosed"
                ]);
            } else {
                $queryArr1 = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `sharedinfo` WHERE `sharecode` = '" . mysqli_escape_string($con, $_REQUEST["sharecode"]) . "'"));
                if ($queryArr1["avaliable"] == 0) {
                    echo json_encode([
                        "status" => "error.shareCodeErrorOrShareIsClosed"
                    ]);
                } else {
                    $queryArr2 = mysqli_fetch_assoc(mysqli_query($con, "SELECT `id`,`name`,`ownerid`,CONVERT(DES_DECRYPT(`content`,'" . mysqli_escape_string($con, constant("encrypt-key")) . "') USING utf8mb4) AS `content`,`lastmodifiedtime` FROM `files` WHERE `id` = " . $queryArr1["fileid"]));
                    if ($queryArr2) {
                        if (mysqli_num_rows(mysqli_query($con, "SELECT * FROM `readsharedfilelog` WHERE `fileid` = " . $queryArr1["fileid"] . " AND `userid` = " . $reqID)) == 0) {
                            // 未打开过
                            mysqli_query($con, "INSERT INTO `readsharedfilelog` VALUES (NULL," . $reqID . "," . $queryArr1["fileid"] . ",CURRENT_TIMESTAMP(),NULL)");
                        }
                        echo json_encode([
                            "status" => "success",
                            "name" => $queryArr2["name"],
                            "fileid" => $queryArr1["fileid"],
                            "ownername" => maskString(mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `users` WHERE id = " . $queryArr2["ownerid"]))["name"]),
                            "lastmodifiedtime" => $queryArr2["lastmodifiedtime"],
                            "content" => $queryArr2["content"],
                            "readtimes" => mysqli_num_rows(mysqli_query($con, "SELECT * FROM `readsharedfilelog` WHERE `fileid` = " . $queryArr1["fileid"]))
                        ]);
                    } else {
                        echo "openSharedFile.error.shareCodeErrorOrShareIsClosed";
                    }
                }
            }
            break;
        case "resave-shared-file":
            mysqli_query($con, "INSERT INTO `files` SELECT NULL,'" . mysqli_escape_string($con, $_REQUEST["newname"]) . "'," . $reqID . ",`content`,CURRENT_TIMESTAMP() FROM `files` WHERE id = " . $_REQUEST["fileid"]);
            createSharedRecord(mysqli_insert_id($con));
            echo "resaveFile.success";
            break;
        default:
            echo "UNKNOWN REQUEST TYPE (<code>" . $_REQUEST["type"] . "</code>)";
            break;
    }
} else {
    echo "INVALID REQUEST METHOD (<code>" . $_SERVER["REQUEST_METHOD"] . "</code>)";
}
// } else {
//     echo "403 Forbidden. The request shall be from <code>localhost</code>.";
// }
