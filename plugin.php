<?php

  /**
    This is the ArchiveWidget plugin.

    This file contains the ArchiveWidget plugin. It provides a widget that displays a monthly archive.

    @package urlaube\archivewidget
    @version 0.1a0
    @author  Yahe <hello@yahe.sh>
    @since   0.1a0
  */

  // ===== DO NOT EDIT HERE =====

  // prevent script from getting called directly
  if (!defined("URLAUBE")) { die(""); }

  if (!class_exists("ArchiveWidget")) {
    class ArchiveWidget extends Base implements Plugin {

      // HELPER FUNCTIONS

      protected static function getMonthName($month) {
        $result = $month;

        $monthnames = array( 1 => "Januar",
                             2 => "Februar",
                             3 => "März",
                             4 => "April",
                             5 => "Mai",
                             6 => "Juni",
                             7 => "Juli",
                             8 => "August",
                             9 => "September",
                            10 => "Oktober",
                            11 => "November",
                            12 => "Dezember");

        if (isset($monthnames[$month])) {
          $result = t($monthnames[$month], "ArchiveWidget");
        }

        return $result;
      }

      // RUNTIME FUNCTIONS

      public static function plugin() {
        $result = null;

        $dates = array();

        File::loadContentDir(USER_CONTENT_PATH, true,
                             function ($content) use (&$dates) {
                               $result = null;

                               // check that $content is not hidden
                               if (!istrue(value($content, HIDDEN))) {
                                 // check that $content is not hidden from archive
                                 if (!istrue(value($content, HIDDENFROMARCHIVE))) {
                                   // check that $content is not a relocation
                                   if (null === value($content, RELOCATE)) {
                                     // read the date
                                     $datevalue = value($content, DATE);
                                     if (null !== $datevalue) {
                                       $time = strtotime($datevalue);

                                       // only proceed if DATE is parsable
                                       if (false !== $time) {
                                         $date  = getdate($time);
                                         $index = $date["year"].SP.str_pad($date["mon"], 2, "0", STR_PAD_LEFT);

                                         if (isset($dates[$index])) {
                                           $dates[$index]++;
                                         } else {
                                           $dates[$index] = 1;
                                         }
                                       }
                                     }
                                   }
                                 }
                               }

                               return null;
                             },
                             true);

        if (0 < count($dates)) {
          // sort the dates
          ksort($dates);

          $content = "<div>".NL;
          foreach ($dates as $key => $value) {
            $date = explode(SP, $key);

            $content .= fhtml("  <span class=\"glyphicon glyphicon-time\"></span> <a href=\"%s\">%s %s</a> (%d)".BR.NL,
                              ArchiveHandler::getUri(array(YEAR => $date[0], MONTH => $date[1], PAGE => 1)),
                              static::getMonthName(intval($date[1])),
                              $date[0],
                              $value);
          }
          $content .= "</div>";

          $result = new Content();
          $result->set(CONTENT, $content);
          $result->set(TITLE,   t("Archiv", "ArchiveWidget"));
        }

        return $result;
      }

    }

    // register plugin
    Plugins::register("ArchiveWidget", "plugin", ON_WIDGETS);

    // register translation
    Translate::register(__DIR__.DS."lang".DS, "ArchiveWidget");
  }

