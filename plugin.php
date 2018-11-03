<?php

  /**
    This is the ArchiveWidget plugin.

    This file contains the ArchiveWidget plugin. It provides a widget that
    displays a monthly archive.

    @package urlaube\archivewidget
    @version 0.1a3
    @author  Yahe <hello@yahe.sh>
    @since   0.1a0
  */

  // ===== DO NOT EDIT HERE =====

  // prevent script from getting called directly
  if (!defined("URLAUBE")) { die(""); }

  class ArchiveWidget extends BaseSingleton implements Plugin {

    // HELPER FUNCTIONS

    protected static function getMonthName($month) {
      $result = $month;

      $monthnames = [1  => "Januar",
                     2  => "Februar",
                     3  => "März",
                     4  => "April",
                     5  => "Mai",
                     6  => "Juni",
                     7  => "Juli",
                     8  => "August",
                     9  => "September",
                     10 => "Oktober",
                     11 => "November",
                     12 => "Dezember"];

      if (isset($monthnames[$month])) {
        $result = t($monthnames[$month], ArchiveWidget::class);
      }

      return $result;
    }

    // RUNTIME FUNCTIONS

    public static function plugin() {
      $result = null;

      $dates = [];
      if (!getcache(null, $dates, static::class)) {
        callcontent(null, true, true,
                    function ($content) use (&$dates) {
                      $result = null;

                      // check that $content is not hidden
                      if (!istrue(value($content, HidePlugin::HIDDEN))) {
                        // check that $content is not hidden from archive
                        if (!istrue(value($content, HidePlugin::HIDDENFROMARCHIVE))) {
                          // check that $content is not a relocation
                          if (null === value($content, RelocatePlugin::RELOCATE)) {
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
                    });

        setcache(null, $dates, static::class);
      }

      if (0 < count($dates)) {
        // sort the dates
        ksort($dates);

        $content = fhtml("<div>".NL);
        foreach ($dates as $key => $value) {
          $date = explode(SP, $key);

          $metadata = new Content();
          $metadata->set(ArchiveHandler::YEAR,  $date[0]);
          $metadata->set(ArchiveHandler::MONTH, $date[1]);

          $content .= fhtml("  <span class=\"glyphicon glyphicon-time\"></span> <a href=\"%s\">%s %s</a> (%d)".BR.NL,
                            ArchiveHandler::getUri($metadata),
                            static::getMonthName(intval($date[1])),
                            $date[0],
                            $value);
        }
        $content .= fhtml("</div>");

        $result = new Content();
        $result->set(CONTENT, $content);
        $result->set(TITLE,   t("Archiv", static::class));
      }

      return $result;
    }

  }

  // register plugin
  Plugins::register(ArchiveWidget::class, "plugin", ON_WIDGETS);

  // register translation
  Translate::register(__DIR__.DS."lang".DS, ArchiveWidget::class);
