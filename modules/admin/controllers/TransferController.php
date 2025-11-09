<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;

class TransferController extends Controller
{
    
     public $enableCsrfValidation = false; // disable CSRF

   public function actionCopyall()
{
    $sourceDb = Yii::$app->db;
    $targetDb = Yii::$app->db2;

    // Disable foreign key checks
    $targetDb->createCommand('SET foreign_key_checks = 0')->execute();

    $tables = $sourceDb->createCommand('SHOW TABLES')->queryColumn();

    foreach ($tables as $tableName) {
        echo "<b>Copying table:</b> $tableName<br>";

        try {
            $createStmt = $sourceDb->createCommand("SHOW CREATE TABLE `$tableName`")->queryOne();
            $createSql = $createStmt['Create Table'];

            // Drop and recreate table
            $targetDb->createCommand("DROP TABLE IF EXISTS `$tableName`")->execute();
            $targetDb->createCommand($createSql)->execute();

            // Copy data
            $rows = $sourceDb->createCommand("SELECT * FROM `$tableName`")->queryAll();
            foreach ($rows as $row) {
                $targetDb->createCommand()->insert($tableName, $row)->execute();
            }

            echo "✅ Copied $tableName (" . count($rows) . " rows)<br><br>";
        } catch (\Exception $e) {
            echo "❌ Error copying $tableName: " . $e->getMessage() . "<br><br>";
        }
    }

    // Enable foreign key checks again
    $targetDb->createCommand('SET foreign_key_checks = 1')->execute();

    echo "<hr><b>🎉 All tables copied!</b>";
    Yii::$app->end();
}
}
