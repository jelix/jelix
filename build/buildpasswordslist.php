<?php

/**
 * Build a Javascript list of most used passwords
 *
 * @author       Laurent Jouanneau <laurent@jelix.org>
 * @copyright    2023 Laurent Jouanneau
 *
 * @see https://en.wikipedia.org/wiki/Wikipedia:10,000_most_common_passwords
 * @link         https://jelix.org
 * @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */
$list = file(__DIR__.'/most_used_passwords.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
$content = '';
foreach($list as $i => $term) {
    $content.= ',"'.$term.'"';
    if (($i % 15)===0) {
        $content.="\n";
    }
}
$content[0] = '[';
file_put_contents(__DIR__.'/../lib/jelix-www/js/jforms/password-list.js', 'var JelixPasswordEditorPasswords='.$content.'];' );
