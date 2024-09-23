<?php

namespace FileHtmlProperty;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;

Loc::loadMessages(__FILE__);

class FileHtmlProperty
{

    static function GetIBlockPropertyDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'FileHtmlProperty',
            'DESCRIPTION' => Loc::getMessage('CUSTOM_PROPERTY_DESCRIRTION'),
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
        );
    }

    public static function GetPropertyFieldHtml($propertyFields, $value, $htmlControlName)
    {
        if ($htmlControlName['MODE'] == 'FORM_FILL' && \Bitrix\Main\Loader::includeModule('fileman')) {
            return self::createFileHtmlControl($value, $htmlControlName);
        } else {
            return false;
        }
    }

    public static function GetPropertyFieldHtmlMulty($propertyFields, $values, $htmlControlName)
    {
        if ($htmlControlName['MODE'] == 'FORM_FILL' && \Bitrix\Main\Loader::includeModule('fileman')) {
            $inputHtml = '';
            $counter = 1;
            foreach ($values as $value) {
                $inputHtml .= self::createFileHtmlControl($value, $htmlControlName, $counter);
                $counter++;
            }

            $inputHtml .= self::createFileHtmlControl(null, $htmlControlName, $counter);

            return $inputHtml;
        } else {
            return false;
        }
    }

    private static function createFileHtmlControl($value, $htmlControlName, $index = false)
    { 

        $value = unserialize($value['VALUE']);

       $inputHtml =  \Bitrix\Main\UI\FileInput::createInstance(array(
            "name" => str_replace('[VALUE]', '[FILE]', $htmlControlName['VALUE']),
            "description" => false,
            "upload" => true,
            "allowUpload" => "I",
            "medialib" => false,
            "fileDialog" => false,
            "cloud" => false,
            "delete" => true,
            "maxCount" => 1
        ))->show(
            $value['FILE']
        );

        $inputHtml .= '<input type="hidden" name="' . str_replace('[VALUE]', '[OLD_FILE]', $htmlControlName['VALUE']) . '" value="' . $value['FILE'] . '">';
        $inputHtml .= '<input type="text" size="30" name="' . str_replace('[VALUE]', '[SIMPLE]', $htmlControlName['VALUE']) . '" value="' . $value['SIMPLE'] . '">';

        ob_start();
       \CFileMan::AddHTMLEditorFrame(
            preg_replace("/[\[\]]/i", "_", $htmlControlName['VALUE'] . '[TEXT]'),
            $value['TEXT'],
            preg_replace("/[\[\]]/i", "_", $htmlControlName['VALUE'] . '[TYPE]'),
            strlen($value['TYPE'] == 'html') ? 'html' : 'text',
            array(
                'height' => 100,
            )
        );
        
        $editor = ob_get_contents();
        ob_end_clean();

        return $inputHtml . $editor;
    }

    public static function ConvertToDB($propertyFields, $propertyValue)
    {
        $return = false;

        if ($propertyValue['VALUE'] && is_array($propertyValue['VALUE']) && (is_array($propertyValue['FILE']) && $propertyValue['FILE']['size'] || $propertyValue['VALUE']['TEXT']) || $propertyValue['SIMPLE']) {
            $propertyValue['VALUE']['SIMPLE'] =  $propertyValue['SIMPLE'];
            $request = Context::getCurrent()->getRequest();
            $requestProps = $request->get('PROP');
            $requestDelProps = $request->get('PROP_del');
            $propsArr = [];
            if ($requestProps && is_array($requestProps)) {
                if ($requestProps[$propertyFields['ID']]) {
                    foreach ($requestProps[$propertyFields['ID']] as $key => $valueAr) {
                        if ($valueAr['OLD_FILE']) 
                            $propsArr[$valueAr['OLD_FILE']] = $key;
                    }

                }
            }
            $delArr = [];
            if ($requestDelProps && is_array($requestDelProps)) {
                if ($requestDelProps[$propertyFields['ID']] && is_array($requestDelProps[$propertyFields['ID']])) {
                    foreach ($requestDelProps[$propertyFields['ID']] as $key => $valueAr) {
                        if ($valueAr['FILE'] == 'Y') 
                            $delArr[] = $key;
                    }
                }
            }
            if (is_array($propertyValue['FILE']) && $propertyValue['FILE']['size']) {
                if (isset($propertyValue['FILE']['tmp_name']) && !file_exists($propertyValue['FILE']['tmp_name'])) {
                    $tmpFilesDir = \CTempFile::GetAbsoluteRoot();
                    $propertyValue['FILE']['tmp_name'] = $tmpFilesDir . $propertyValue['FILE']['tmp_name'];
                    $propertyValue['FILE']['MODULE_ID'] = 'iblock';
                    $fid = \CFile::SaveFile($propertyValue['FILE'], 'iblock');
                    if ($fid) {
                        $propertyValue['VALUE']['FILE'] = $fid;
                        if ($propertyValue['OLD_FILE']) {
                            \CFile::Delete($propertyValue['OLD_FILE']);
                        }
                    }
                }
            } elseif ($propertyValue['OLD_FILE'] && $propsArr[$propertyValue['OLD_FILE']] && in_array($propsArr[$propertyValue['OLD_FILE']], $delArr)) {
                $propertyValue['VALUE']['FILE'] = '';
                \CFile::Delete($propertyValue['OLD_FILE']);
            } elseif ($propertyValue['OLD_FILE']) {
                $propertyValue['VALUE']['FILE'] = $propertyValue['OLD_FILE'];
            } else {
                $propertyValue['VALUE']['FILE'] = '';
            }

            if( is_array($propertyValue) && array_key_exists('VALUE', $propertyValue) )
            {
                $return = array(
                    'VALUE' => serialize($propertyValue['VALUE'])
                );
            }
        }
        return $return; 

    }

    static function ConvertFromDB($arProperty, $value)
    {
        $return = false;
         
        if(!is_array($value['VALUE']))
        {
            $return = array(
                'VALUE' => unserialize($value['VALUE'])
            );
        }
         
        return $return;
    }

}