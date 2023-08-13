<?php

namespace Iyuu\Spider\Support;

use DOMDocument;
use DOMXPath;

/**
 * 选择器
 */
class Selector
{
    /**
     * 错误描述
     * @var string|null
     */
    public static ?string $error = null;

    /**
     * @param string $html
     * @param string $selector
     * @param string $selector_type
     * @return array|string|null
     */
    public static function select(string $html, string $selector, string $selector_type = 'xpath')
    {
        if (empty($html) || empty($selector)) {
            return null;
        }

        $selector_type = strtolower($selector_type);
        switch ($selector_type) {
            case 'xpath':
                return self::_xpath_select($html, $selector);
            case 'regex':
                return self::_regex_select($html, $selector);
            default:
                return null;
        }
    }

    /**
     * xpath选择器
     * @param string $html
     * @param string $selector
     * @param bool $remove
     * @return null|string|array
     */
    private static function _xpath_select(string $html, string $selector, bool $remove = false)
    {
        $dom = new DOMDocument();
        // 禁用标准的 libxml 错误
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        // 清空 libxml 错误缓冲
        libxml_clear_errors();
        $xpath = new DOMXpath($dom);

        $elements = @$xpath->query($selector);
        if ($elements === false) {
            self::$error = "the selector in the xpath({$selector}) syntax errors";
            // 不应该返回false，因为isset(false)为true，更不能通过 !$values 去判断，因为!0为true，所以这里只能返回null
            return null;
        }

        $result = [];
        if (is_object($elements)) {
            foreach ($elements as $element) {
                // 如果是删除操作，取一整块代码
                if ($remove) {
                    $content = $dom->saveXml($element);
                } else {
                    $nodeName = $element->nodeName;
                    $nodeType = $element->nodeType;     // 1.Element 2.Attribute 3.Text
                    //$nodeAttr = $element->getAttribute('src');
                    // 如果是img标签，直接取src值
                    if ($nodeType == 1 && in_array($nodeName, ['img'])) {
                        $content = $element->getAttribute('src');
                    } // 如果是标签属性，直接取节点值
                    elseif ($nodeType == 2 || $nodeType == 3 || $nodeType == 4) {
                        $content = $element->nodeValue;
                    } else {
                        // 保留nodeValue里的html符号，给children二次提取
                        $content = $dom->saveXml($element);
                        $content = preg_replace(array("#^<{$nodeName}.*>#isU", "#</{$nodeName}>$#isU"), array('', ''), $content);
                    }
                }
                $result[] = $content;
            }
        }
        if (empty($result)) {
            return null;
        }
        // 如果只有一个元素就直接返回string，否则返回数组
        return count($result) > 1 ? $result : $result[0];
    }

    /**
     * 正则选择器
     * @param string $html
     * @param string $selector
     * @param bool $remove
     * @return null|string|array
     */
    private static function _regex_select(string $html, string $selector, bool $remove = false)
    {
        if (@preg_match_all($selector, $html, $out) === false) {
            self::$error = "the selector in the regex({$selector}) syntax errors";
            return null;
        }
        $count = count($out);
        $result = [];
        // 一个都没有匹配到
        if ($count === 0) {
            return null;
        } // 只匹配一个，就是只有一个 ()
        elseif ($count == 2) {
            // 删除的话取匹配到的所有内容
            if ($remove) {
                $result = $out[0];
            } else {
                $result = $out[1];
            }
        } else {
            for ($i = 1; $i < $count; $i++) {
                // 如果只有一个元素，就直接返回好了
                $result[] = count($out[$i]) > 1 ? $out[$i] : $out[$i][0];
            }
        }
        if (empty($result)) {
            return null;
        }

        return count($result) > 1 ? $result : $result[0];
    }

    /**
     * 移除
     * @param string $html
     * @param string $selector
     * @param string $selector_type
     * @return null|string|array
     */
    public static function remove(string $html, string $selector, string $selector_type = 'xpath')
    {
        if (empty($html) || empty($selector)) {
            return null;
        }
        $selector_type = strtolower($selector_type);
        switch ($selector_type) {
            case 'xpath':
                $_html = self::_xpath_select($html, $selector, true);
                break;
            case 'regex':
                $_html = self::_regex_select($html, $selector, true);
                break;
            default:
                return null;
        }
        return str_replace($_html, '', $html);
    }
}
