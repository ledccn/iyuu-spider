#!/bin/sh
current_dir=$(cd $(dirname $0); pwd)
echo $current_dir
cd $current_dir
if [ $1 ]; then
  PHP_BINARY=$1
else
  PHP_BINARY="php81"
fi

#$PHP_BINARY webman spider 1ptba --type=rss
#$PHP_BINARY webman spider 52pt --type=rss
#$PHP_BINARY webman spider audiences --type=rss
#$PHP_BINARY webman spider beitai --type=rss
#$PHP_BINARY webman spider btschool --type=rss
#$PHP_BINARY webman spider byr --type=rss

$PHP_BINARY webman spider carpt --type=rss
#$PHP_BINARY webman spider chdbits --type=rss

$PHP_BINARY webman spider cyanbug --type=rss
$PHP_BINARY webman spider dajiao --type=rss

#$PHP_BINARY webman spider dicmusic --type=rss
#$PHP_BINARY webman spider discfan --type=rss
#$PHP_BINARY webman spider dmhy --type=rss
#$PHP_BINARY webman spider dragonhd --type=rss
#$PHP_BINARY webman spider eastgame --type=rss
#$PHP_BINARY webman spider gainbound --type=rss
#$PHP_BINARY webman spider greatposterwall --type=rss
#$PHP_BINARY webman spider haidan --type=rss
#$PHP_BINARY webman spider hares --type=rss
#$PHP_BINARY webman spider hd-torrents --type=rss
#$PHP_BINARY webman spider hd4fans --type=rss
#$PHP_BINARY webman spider hdarea --type=rss
#$PHP_BINARY webman spider hdatmos --type=rss
#$PHP_BINARY webman spider hdbd --type=rss
#$PHP_BINARY webman spider hdbits --type=rss
#$PHP_BINARY webman spider hdchina --type=rss
#$PHP_BINARY webman spider hdcity --type=rss
#$PHP_BINARY webman spider hddolby --type=rss
#$PHP_BINARY webman spider hdfans --type=rss
#$PHP_BINARY webman spider hdhome --type=rss

$PHP_BINARY webman spider hdmayi --type=rss
#$PHP_BINARY webman spider hdpost --type=rss
#$PHP_BINARY webman spider hdpt --type=rss
#$PHP_BINARY webman spider hdroute --type=rss
#$PHP_BINARY webman spider hdsky --type=rss
#$PHP_BINARY webman spider hdtime --type=rss
#$PHP_BINARY webman spider hdvideo --type=rss
#$PHP_BINARY webman spider hdzone --type=rss
#$PHP_BINARY webman spider hhanclub --type=rss
#$PHP_BINARY webman spider hitpt --type=rss
#$PHP_BINARY webman spider hudbt --type=rss
#$PHP_BINARY webman spider joyhd --type=rss
#$PHP_BINARY webman spider keepfrds --type=rss
#$PHP_BINARY webman spider leaguehd --type=rss

$PHP_BINARY webman spider m-team --type=rss
$PHP_BINARY webman spider monikadesign --type=rss

#$PHP_BINARY webman spider nanyangpt --type=rss
#$PHP_BINARY webman spider nicept --type=rss
#$PHP_BINARY webman spider npupt --type=rss
#$PHP_BINARY webman spider opencd --type=rss
#$PHP_BINARY webman spider oshen --type=rss
#$PHP_BINARY webman spider ourbits --type=rss

$PHP_BINARY webman spider pandapt --type=rss
#$PHP_BINARY webman spider piggo --type=rss
#$PHP_BINARY webman spider pt --type=rss
#$PHP_BINARY webman spider pt0ffcc --type=rss
#$PHP_BINARY webman spider pt2xfree --type=rss
#$PHP_BINARY webman spider ptchina --type=rss
#$PHP_BINARY webman spider pter --type=rss

$PHP_BINARY webman spider ptlsp --type=rss
#$PHP_BINARY webman spider ptmsg --type=rss
#$PHP_BINARY webman spider ptpbd --type=rss
#$PHP_BINARY webman spider ptsbao --type=rss
#$PHP_BINARY webman spider pttime --type=rss
#$PHP_BINARY webman spider redleaves --type=rss

$PHP_BINARY webman spider rousi --type=rss
$PHP_BINARY webman spider shadowflow --type=rss

#$PHP_BINARY webman spider sharkpt --type=rss
#$PHP_BINARY webman spider skyeysnow --type=rss
#$PHP_BINARY webman spider soulvoice --type=rss
#$PHP_BINARY webman spider ssd --type=rss

$PHP_BINARY webman spider tjupt --type=rss

#$PHP_BINARY webman spider torrentccf --type=rss
#$PHP_BINARY webman spider ttg --type=rss

$PHP_BINARY webman spider ubits --type=rss
#$PHP_BINARY webman spider upxin --type=rss
#$PHP_BINARY webman spider wintersakura --type=rss
#$PHP_BINARY webman spider zhuque --type=rss

$PHP_BINARY webman spider zmpt --type=rss
