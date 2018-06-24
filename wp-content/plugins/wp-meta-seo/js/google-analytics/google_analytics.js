"use strict";

// Get the numeric ID
wpmsItemData.getID = function (item) {
    if (wpmsItemData.scope === 'admin-item') {
        if (typeof item.id === "undefined") {
            return 0
        }
        if (typeof item.id.split('-')[ 1 ] === "undefined") {
            return 0;
        } else {
            return item.id.split('-')[ 1 ];
        }
    } else {
        if (typeof item.id === "undefined") {
            return 1;
        }
        if (typeof item.id.split('-')[ 4 ] === "undefined") {
            return 1;
        } else {
            return item.id.split('-')[ 4 ];
        }
    }
};

// Get the selector
wpmsItemData.getSelector = function (scope) {
    if (scope === 'admin-item') {
        return 'a[id^="wpms-"]';
    } else {
        return 'li[id^="wp-admin-bar-wpms"]';
    }
};

wpmsItemData.responsiveDialog = function () {
    var dialog, wWidth, visible;

    visible = jQuery(".ui-dialog:visible");

    // on each visible dialog
    visible.each(function () {
        dialog = jQuery(this).find(".ui-dialog-content").data("ui-dialog");
        // on each fluid dialog
        if (dialog.options.fluid) {
            wWidth = jQuery(window).width();
            // window width vs dialog width
            if (wWidth < (parseInt(dialog.options.maxWidth) + 50)) {
                // don't fill the entire screen
                jQuery(this).css("max-width", "90%");
            } else {
                // maxWidth bug fix
                jQuery(this).css("max-width", dialog.options.maxWidth + "px");
            }
            // change dialog position
            dialog.option("position", dialog.options.position);
        }
    });
};

jQuery.fn.extend({
    wpmsItemReport: function (itemId) {
        var postData, tools, template, reports, slug = "-" + itemId;

        tools = {
            setCookie: function (name, value) {
                var expires, dateItem = new Date();

                if (wpmsItemData.scope === 'admin-widgets') {
                    name = "wpms_wg_" + name;
                } else {
                    name = "wpms_ir_" + name;
                }
                dateItem.setTime(dateItem.getTime() + (24 * 60 * 60 * 1000 * 7));
                expires = "expires=" + dateItem.toUTCString();
                document.cookie = name + "=" + value + "; " + expires + "; path=/";
            },
            getCookie: function (name) {
                var cookie, cookiesArray, i;

                if (wpmsItemData.scope === 'admin-widgets') {
                    name = "wpms_wg_" + name + "=";
                } else {
                    name = "wpms_ir_" + name + "=";
                }
                cookiesArray = document.cookie.split(';');
                for (i = 0; i < cookiesArray.length; i++) {
                    cookie = cookiesArray[ i ];
                    while (cookie.charAt(0) === ' ')
                        cookie = cookie.substring(1);
                    if (cookie.indexOf(name) === 0)
                        return cookie.substring(name.length, cookie.length);
                }
                return false;
            },
            escape: function (str) {
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }
        };

        template = {
            addToolbar: function (id, list ,classbtn) {
                var defaultMetric, defaultDimension, defaultView, output = [];

                if (!list) {
                    return;
                }

                if (!tools.getCookie('default_metric') || !tools.getCookie('default_dimension')) {
                    if (wpmsItemData.scope === 'admin-widgets') {
                        defaultMetric = 'sessions';
                    } else {
                        defaultMetric = 'uniquePageviews';
                    }
                    defaultDimension = '30daysAgo';
                } else {
                    defaultMetric = tools.getCookie('default_metric');
                    defaultDimension = tools.getCookie('default_dimension');
                    defaultView = tools.getCookie('default_view');
                }

                jQuery.each(list, function (key, value) {
                    if (key === defaultMetric || key === defaultDimension || key === defaultView) {
                        output.push('<div class="'+ classbtn +' button button-primary active" data-value="'+ key +'">'+ value +'</div>');
                    } else {
                        output.push('<div class="'+ classbtn +' button" data-value="'+ key +'">'+ value +'</div>');
                    }
                });
                jQuery(id).html(output.join(''));
            },
            init: function () {
                var tpl;
                var $wind = jQuery('#wpms-window' + slug);
                if (!$wind.length) {
                    return;
                }

                tpl = '<div id="wpms-container' + slug + '">';
                tpl += '<div class="wpmsga-btn-toolbar wpmsga-btn-toolbar-date">';
                tpl += '</div>';
                
                tpl += '<div class="wpmsga-btn-toolbar wpmsga-btn-toolbar-report">';
                tpl += '</div>';
                tpl += '<div id="wpms-progressbar' + slug + '"></div>';
                tpl += '<div id="wpms-status' + slug + '"></div>';
                tpl += '<div id="wpms-reports' + slug + '"></div>';
                tpl += '</div>';
                $wind.append(tpl);
        
                template.addToolbar( '.wpmsga-btn-toolbar-date', wpmsItemData.dateList , 'wpmsga_date');
                template.addToolbar( '.wpmsga-btn-toolbar-report', wpmsItemData.reportList , 'wpmsga_report');
            }
        };

        reports = {
            orgChartTableChartData: '',
            orgChartPieChartsData: '',
            geoChartTableChartData: '',
            areaChartBottomStatsData: '',
            realtime: '',
            rtRuns: null,
            i18n: null,
            getTitle: function (scope) {
                if (scope === 'admin-item') {
                    return jQuery('#wpms' + slug).attr("title");
                } else {
                    return document.getElementsByTagName("title")[ 0 ].innerHTML;
                }
            },
            alertMessage: function (msg) {
                jQuery("#wpms-status" + slug).css({
                    "margin-top": "3px",
                    "padding-left": "5px",
                    "height": "auto",
                    "color": "#000",
                    "border-left": "5px solid red"
                }).html(msg);
            },
            areaChartBottomStats: function (response) {
                reports.areaChartBottomStatsData = response;
                if (jQuery.isArray(response)) {
                    if (!jQuery.isNumeric(response[ 0 ])) {
                        if (jQuery.isArray(response[ 0 ])) {
                            jQuery('#wpms-reports' + slug).show();
                            if (postData.query === 'visitBounceRate,bottomstats') {
                                reports.drawAreaChart(response[ 0 ], true);
                            } else {
                                reports.drawAreaChart(response[ 0 ], false);
                            }
                        } else {
                            reports.throwDebug(response[ 0 ]);
                        }
                    } else {
                        jQuery('#wpms-reports' + slug).show();
                        reports.throwError('#wpms-areachart' + slug, response[ 0 ], "125px");
                    }
                    if (!jQuery.isNumeric(response[ 1 ])) {
                        if (jQuery.isArray(response[ 1 ])) {
                            jQuery('#wpms-reports' + slug).show();
                            reports.drawBottomStats(response[ 1 ]);
                        } else {
                            reports.throwDebug(response[ 1 ]);
                        }
                    } else {
                        jQuery('#wpms-reports' + slug).show();
                        reports.throwError('#wpms-bottomstats' + slug, response[ 1 ], "40px");
                    }
                } else {
                    reports.throwDebug(response);
                }
                NProgress.done();

            },
            orgChartPieCharts: function (response) {
                var i = 0;
                reports.orgChartPieChartsData = response;
                if (jQuery.isArray(response)) {
                    if (!jQuery.isNumeric(response[ 0 ])) {
                        if (jQuery.isArray(response[ 0 ])) {
                            jQuery('#wpms-reports' + slug).show();
                            reports.drawOrgChart(response[ 0 ]);
                        } else {
                            reports.throwDebug(response[ 0 ]);
                        }
                    } else {
                        jQuery('#wpms-reports' + slug).show();
                        reports.throwError('#wpms-orgchart' + slug, response[ 0 ], "125px");
                    }

                    for (i = 1; i < response.length; i++) {
                        if (!jQuery.isNumeric(response[ i ])) {
                            if (jQuery.isArray(response[ i ])) {
                                jQuery('#wpms-reports' + slug).show();
                                reports.drawPieChart('piechart-' + i, response[ i ], reports.i18n[ i ]);
                            } else {
                                reports.throwDebug(response[ i ]);
                            }
                        } else {
                            jQuery('#wpms-reports' + slug).show();
                            reports.throwError('#wpms-piechart-' + i + slug, response[ i ], "80px");
                        }
                    }
                } else {
                    reports.throwDebug(response);
                }
                NProgress.done();
            },
            geoChartTableChart: function (response) {
                reports.geoChartTableChartData = response;
                if (jQuery.isArray(response)) {
                    if (!jQuery.isNumeric(response[ 0 ])) {
                        if (jQuery.isArray(response[ 0 ])) {
                            jQuery('#wpms-reports' + slug).show();
                            reports.drawGeoChart(response[ 0 ]);
                            reports.drawTableChart(response[ 0 ]);
                        } else {
                            reports.throwDebug(response[ 0 ]);
                        }
                    } else {
                        jQuery('#wpms-reports' + slug).show();
                        reports.throwError('#wpms-geochart' + slug, response[ 0 ], "125px");
                        reports.throwError('#wpms-tablechart' + slug, response[ 0 ], "125px");
                    }
                } else {
                    reports.throwDebug(response);
                }
                NProgress.done();
            },
            orgChartTableChart: function (response) {
                reports.orgChartTableChartData = response;
                if (jQuery.isArray(response)) {
                    if (!jQuery.isNumeric(response[ 0 ])) {
                        if (jQuery.isArray(response[ 0 ])) {
                            jQuery('#wpms-reports' + slug).show();
                            reports.drawOrgChart(response[ 0 ]);
                        } else {
                            reports.throwDebug(response[ 0 ]);
                        }
                    } else {
                        jQuery('#wpms-reports' + slug).show();
                        reports.throwError('#wpms-orgchart' + slug, response[ 0 ], "125px");
                    }

                    if (!jQuery.isNumeric(response[ 1 ])) {
                        if (jQuery.isArray(response[ 1 ])) {
                            reports.drawTableChart(response[ 1 ]);
                        } else {
                            reports.throwDebug(response[ 1 ]);
                        }
                    } else {
                        reports.throwError('#wpms-tablechart' + slug, response[ 1 ], "125px");
                    }
                } else {
                    reports.throwDebug(response);
                }
                NProgress.done();
            },
            drawTableChart: function (data) {
                var chartData, options, chart;

                chartData = google.visualization.arrayToDataTable(data);
                options = {
                    page: 'enable',
                    pageSize: 10,
                    width: '100%',
                    allowHtml: true
                };
                chart = new google.visualization.Table(document.getElementById('wpms-tablechart' + slug));

                chart.draw(chartData, options);
            },
            drawOrgChart: function (data) {
                var chartData, options, chart;

                chartData = google.visualization.arrayToDataTable(data);
                options = {
                    allowCollapse: true,
                    allowHtml: true,
                    height: '100%'
                };
                chart = new google.visualization.OrgChart(document.getElementById('wpms-orgchart' + slug));

                chart.draw(chartData, options);
            },
            drawPieChart: function (id, data, title) {
                var chartData, options, chart;

                chartData = google.visualization.arrayToDataTable(data);
                options = {
                    is3D: false,
                    tooltipText: 'percentage',
                    legend: 'none',
                    chartArea: {
                        width: '99%',
                        height: '80%'
                    },
                    title: title,
                    pieSliceText: 'value',
                    colors: wpmsItemData.colorVariations
                };
                chart = new google.visualization.PieChart(document.getElementById('wpms-' + id + slug));

                chart.draw(chartData, options);
            },
            drawGeoChart: function (data) {
                var chartData, options, chart;

                chartData = google.visualization.arrayToDataTable(data);
                options = {
                    chartArea: {
                        width: '99%',
                        height: '90%'
                    },
                    colors: [wpmsItemData.colorVariations[ 5 ], wpmsItemData.colorVariations[ 4 ]]
                };
                if (wpmsItemData.region) {
                    options.region = wpmsItemData.region;
                    options.displayMode = 'markers';
                    options.datalessRegionColor = 'EFEFEF';
                }
                chart = new google.visualization.GeoChart(document.getElementById('wpms-geochart' + slug));

                chart.draw(chartData, options);
            },
            drawAreaChart: function (data, format) {
                var chartData, options, chart, formatter;

                chartData = google.visualization.arrayToDataTable(data);

                if (format) {
                    formatter = new google.visualization.NumberFormat({
                        suffix: '%',
                        fractionDigits: 2
                    });

                    formatter.format(chartData, 1);
                }

                options = {
                    legend: {
                        position: 'none'
                    },
                    pointSize: 3,
                    colors: [wpmsItemData.colorVariations[ 0 ], wpmsItemData.colorVariations[ 4 ]],
                    chartArea: {
                        width: '99%',
                        height: '90%'
                    },
                    vAxis: {
                        textPosition: "in",
                        minValue: 0
                    },
                    hAxis: {
                        textPosition: 'none'
                    }
                };
                chart = new google.visualization.AreaChart(document.getElementById('wpms-areachart' + slug));

                chart.draw(chartData, options);
            },
            drawBottomStats: function (data) {
                jQuery("#gdsessions" + slug).html(data[ 0 ]);
                jQuery("#gdusers" + slug).html(data[ 1 ]);
                jQuery("#gdpageviews" + slug).html(data[ 2 ]);
                jQuery("#gdbouncerate" + slug).html(data[ 3 ] + "%");
                jQuery("#gdorganicsearch" + slug).html(data[ 4 ]);
                jQuery("#gdpagespervisit" + slug).html(data[ 5 ]);
            },
            rtOnlyUniqueValues: function (value, index, self) {
                return self.indexOf(value) === index;
            },
            rtCountSessions: function (rtData, searchValue) {
                var count = 0, i;

                for (i = 0; i < rtData[ "rows" ].length; i++) {
                    if (jQuery.inArray(searchValue, rtData[ "rows" ][ i ]) > -1) {
                        count += parseInt(rtData[ "rows" ][ i ][ 6 ]);
                    }
                }
                return count;
            },
            rtGenerateTooltip: function (rtData) {
                var count = 0, table = "", i;

                for (i = 0; i < rtData.length; i++) {
                    count += parseInt(rtData[ i ].count);
                    table += "<tr><td class='wpms-pgdetailsl'>" + rtData[ i ].value + "</td><td class='wpms-pgdetailsr'>" + rtData[ i ].count + "</td></tr>";
                }
                if (count) {
                    return ("<table>" + table + "</table>");
                } else {
                    return ("");
                }
            },
            rtPageDetails: function (rtData, searchValue) {
                var pageTitle, i, countrfr = 0, countkwd = 0, countdrt = 0, countscl = 0, countcpg = 0, tablerfr = "", tablekwd = "", tablescl = "", tablecpg = "", tabledrt = "";

                rtData = rtData[ "rows" ];

                for (i = 0; i < rtData.length; i++) {

                    if (rtData[ i ][ 0 ] === searchValue) {
                        pageTitle = rtData[ i ][ 5 ];

                        switch (rtData[ i ][ 3 ]) {

                            case "REFERRAL":
                                countrfr += parseInt(rtData[ i ][ 6 ]);
                                tablerfr += "<tr><td class='wpms-pgdetailsl'>" + rtData[ i ][ 1 ] + "</td><td class='wpms-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
                                break;
                            case "ORGANIC":
                                countkwd += parseInt(rtData[ i ][ 6 ]);
                                tablekwd += "<tr><td class='wpms-pgdetailsl'>" + rtData[ i ][ 2 ] + "</td><td class='wpms-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
                                break;
                            case "SOCIAL":
                                countscl += parseInt(rtData[ i ][ 6 ]);
                                tablescl += "<tr><td class='wpms-pgdetailsl'>" + rtData[ i ][ 1 ] + "</td><td class='wpms-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
                                break;
                            case "CUSTOM":
                                countcpg += parseInt(rtData[ i ][ 6 ]);
                                tablecpg += "<tr><td class='wpms-pgdetailsl'>" + rtData[ i ][ 1 ] + "</td><td class='wpms-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
                                break;
                            case "DIRECT":
                                countdrt += parseInt(rtData[ i ][ 6 ]);
                                break;
                        }
                    }
                }

                if (countrfr) {
                    tablerfr = "<table><tr><td>" + reports.i18n[ 0 ] + "(" + countrfr + ")</td></tr>" + tablerfr + "</table><br />";
                }
                if (countkwd) {
                    tablekwd = "<table><tr><td>" + reports.i18n[ 1 ] + "(" + countkwd + ")</td></tr>" + tablekwd + "</table><br />";
                }
                if (countscl) {
                    tablescl = "<table><tr><td>" + reports.i18n[ 2 ] + "(" + countscl + ")</td></tr>" + tablescl + "</table><br />";
                }
                if (countcpg) {
                    tablecpg = "<table><tr><td>" + reports.i18n[ 3 ] + "(" + countcpg + ")</td></tr>" + tablecpg + "</table><br />";
                }
                if (countdrt) {
                    tabledrt = "<table><tr><td>" + reports.i18n[ 4 ] + "(" + countdrt + ")</td></tr></table><br />";
                }
                return ("<p><center><strong>" + pageTitle + "</strong></center></p>" + tablerfr + tablekwd + tablescl + tablecpg + tabledrt);
            },
            rtRefresh: function () {
                if (reports.render.focusFlag) {
                    postData.from = false;
                    postData.to = false;
                    postData.query = 'realtime';
                    jQuery.post(wpmsItemData.ajaxurl, postData, function (response) {
                        if (jQuery.isArray(response)) {
                            jQuery('#wpms-reports' + slug).show();
                            reports.realtime = response[ 0 ];
                            reports.drawRealtime(reports.realtime);
                        } else {
                            reports.throwDebug(response);
                        }

                        NProgress.done();

                    });
                }
            },
            drawRealtime: function (rtData) {
                var rtInfoRight, uPagePath, uReferrals, uKeywords, uSocial, uCustom, i, pagepath = [], referrals = [], keywords = [], social = [], visittype = [], custom = [], uPagePathStats = [], pgStatsTable, uReferrals = [], uKeywords = [], uSocial = [], uCustom = [], uVisitType = ["REFERRAL", "ORGANIC", "SOCIAL", "CUSTOM"], uVisitorType = ["DIRECT", "NEW"];
                rtData = rtData[ 0 ];

                if (jQuery.isNumeric(rtData) || typeof rtData === "undefined") {
                    rtData = [];
                    rtData[ "totalsForAllResults" ] = [];
                    rtData[ "totalsForAllResults" ][ "rt:activeUsers" ] = "0";
                    rtData[ "rows" ] = [];
                }

                if (rtData[ "totalsForAllResults" ][ "rt:activeUsers" ] !== document.getElementById("wpms-online").innerHTML) {
                    jQuery("#wpms-online").fadeOut("slow");
                    jQuery("#wpms-online").fadeOut(500);
                    jQuery("#wpms-online").fadeOut("slow", function () {
                        if ((parseInt(rtData[ "totalsForAllResults" ][ "rt:activeUsers" ])) < (parseInt(document.getElementById("wpms-online").innerHTML))) {
                            jQuery("#wpms-online").css({
                                'background-color': '#FFE8E8'
                            });
                        } else {
                            jQuery("#wpms-online").css({
                                'background-color': '#E0FFEC'
                            });
                        }
                        document.getElementById("wpms-online").innerHTML = rtData[ "totalsForAllResults" ][ "rt:activeUsers" ];
                    });
                    jQuery("#wpms-online").fadeIn("slow");
                    jQuery("#wpms-online").fadeIn(500);
                    jQuery("#wpms-online").fadeIn("slow", function () {
                        jQuery("#wpms-online").css({
                            'background-color': '#FFFFFF'
                        });
                    });
                }

                if (rtData[ "totalsForAllResults" ][ "rt:activeUsers" ] == 0) {
                    rtData[ "rows" ] = [];
                }

                for (i = 0; i < rtData[ "rows" ].length; i++) {
                    pagepath.push(rtData[ "rows" ][ i ][ 0 ]);
                    if (rtData[ "rows" ][ i ][ 3 ] === "REFERRAL") {
                        referrals.push(rtData[ "rows" ][ i ][ 1 ]);
                    }
                    if (rtData[ "rows" ][ i ][ 3 ] === "ORGANIC") {
                        keywords.push(rtData[ "rows" ][ i ][ 2 ]);
                    }
                    if (rtData[ "rows" ][ i ][ 3 ] === "SOCIAL") {
                        social.push(rtData[ "rows" ][ i ][ 1 ]);
                    }
                    if (rtData[ "rows" ][ i ][ 3 ] === "CUSTOM") {
                        custom.push(rtData[ "rows" ][ i ][ 1 ]);
                    }
                    visittype.push(rtData[ "rows" ][ i ][ 3 ]);
                }

                uPagePath = pagepath.filter(reports.rtOnlyUniqueValues);
                for (i = 0; i < uPagePath.length; i++) {
                    uPagePathStats[ i ] = {
                        "pagepath": uPagePath[ i ],
                        "count": reports.rtCountSessions(rtData, uPagePath[ i ])
                    }
                }
                uPagePathStats.sort(function (a, b) {
                    return b.count - a.count
                });

                pgStatsTable = "";
                for (i = 0; i < uPagePathStats.length; i++) {
                    if (i < wpmsItemData.rtLimitPages) {
                        pgStatsTable += '<div class="wpms-pline"><div class="wpms-pleft"><a href="#" data-wpms="' + reports.rtPageDetails(rtData, uPagePathStats[ i ].pagepath) + '">' + uPagePathStats[ i ].pagepath.substring(0, 70) + '</a></div><div class="wpms-pright">' + uPagePathStats[ i ].count + '</div></div>';
                    }
                }
                document.getElementById("wpms-pages").innerHTML = '<br /><div class="wpms-pg">' + pgStatsTable + '</div>';

                uReferrals = referrals.filter(reports.rtOnlyUniqueValues);
                for (i = 0; i < uReferrals.length; i++) {
                    uReferrals[ i ] = {
                        "value": uReferrals[ i ],
                        "count": reports.rtCountSessions(rtData, uReferrals[ i ])
                    };
                }
                uReferrals.sort(function (a, b) {
                    return b.count - a.count
                });

                uKeywords = keywords.filter(reports.rtOnlyUniqueValues);
                for (i = 0; i < uKeywords.length; i++) {
                    uKeywords[ i ] = {
                        "value": uKeywords[ i ],
                        "count": reports.rtCountSessions(rtData, uKeywords[ i ])
                    };
                }
                uKeywords.sort(function (a, b) {
                    return b.count - a.count
                });

                uSocial = social.filter(reports.rtOnlyUniqueValues);
                for (i = 0; i < uSocial.length; i++) {
                    uSocial[ i ] = {
                        "value": uSocial[ i ],
                        "count": reports.rtCountSessions(rtData, uSocial[ i ])
                    };
                }
                uSocial.sort(function (a, b) {
                    return b.count - a.count
                });

                uCustom = custom.filter(reports.rtOnlyUniqueValues);
                for (i = 0; i < uCustom.length; i++) {
                    uCustom[ i ] = {
                        "value": uCustom[ i ],
                        "count": reports.rtCountSessions(rtData, uCustom[ i ])
                    };
                }
                uCustom.sort(function (a, b) {
                    return b.count - a.count
                });

                rtInfoRight = '<div class="wpms-bigtext"><a href="#" data-wpms="' + reports.rtGenerateTooltip(uReferrals) + '"><div class="wpms-bleft">' + reports.i18n[ 0 ] + '</a></div><div class="wpms-bright">' + reports.rtCountSessions(rtData, uVisitType[ 0 ]) + '</div></div>';
                rtInfoRight += '<div class="wpms-bigtext"><a href="#" data-wpms="' + reports.rtGenerateTooltip(uKeywords) + '"><div class="wpms-bleft">' + reports.i18n[ 1 ] + '</a></div><div class="wpms-bright">' + reports.rtCountSessions(rtData, uVisitType[ 1 ]) + '</div></div>';
                rtInfoRight += '<div class="wpms-bigtext"><a href="#" data-wpms="' + reports.rtGenerateTooltip(uSocial) + '"><div class="wpms-bleft">' + reports.i18n[ 2 ] + '</a></div><div class="wpms-bright">' + reports.rtCountSessions(rtData, uVisitType[ 2 ]) + '</div></div>';
                rtInfoRight += '<div class="wpms-bigtext"><a href="#" data-wpms="' + reports.rtGenerateTooltip(uCustom) + '"><div class="wpms-bleft">' + reports.i18n[ 3 ] + '</a></div><div class="wpms-bright">' + reports.rtCountSessions(rtData, uVisitType[ 3 ]) + '</div></div>';

                rtInfoRight += '<div class="wpms-bigtext"><div class="wpms-bleft">' + reports.i18n[ 4 ] + '</div><div class="wpms-bright">' + reports.rtCountSessions(rtData, uVisitorType[ 0 ]) + '</div></div>';
                rtInfoRight += '<div class="wpms-bigtext"><div class="wpms-bleft">' + reports.i18n[ 5 ] + '</div><div class="wpms-bright">' + reports.rtCountSessions(rtData, uVisitorType[ 1 ]) + '</div></div>';

                document.getElementById("wpms-tdo-right").innerHTML = rtInfoRight;
            },
            throwDebug: function (response) {
                jQuery("#wpms-status" + slug).css({
                    "margin-top": "3px",
                    "padding-left": "5px",
                    "height": "auto",
                    "color": "#000",
                    "border-left": "5px solid red"
                });
                if (response == '-24') {
                    jQuery("#wpms-status" + slug).html(wpmsItemData.i18n[ 15 ]);
                } else if(response == '-99') {
                    
                } else {
                    jQuery("#wpms-reports" + slug).css({
                        "background-color": "#F7F7F7",
                        "height": "auto",
                        "margin-top": "10px",
                        "padding-top": "50px",
                        "padding-bottom": "50px",
                        "color": "#000",
                        "text-align": "center"
                    }).html(response).show();
                    jQuery("#wpms-status" + slug).html(wpmsItemData.i18n[ 11 ]);
                    postData = {
                        action: 'wpms_set_error',
                        response: response,
                        wpms_security_set_error: wpmsItemData.security
                    };
                    jQuery.post(wpmsItemData.ajaxurl, postData);
                }
            },
            throwError: function (target, response, p) {
                jQuery(target).css({
                    "background-color": "#F7F7F7",
                    "height": "auto",
                    "padding-top": p,
                    "padding-bottom": p,
                    "color": "#000",
                    "text-align": "center"
                });
                if (response == -21) {
                    jQuery(target).html(wpmsItemData.i18n[ 12 ] + ' (' + response + ')');
                } else {
                    jQuery(target).html(wpmsItemData.i18n[ 13 ] + ' (' + response + ')');
                }
            },
            render: function (view, period, query) {
                var projectId, from, to, tpl;

                if (period === 'realtime') {
                    jQuery('#wpms-sel-report' + slug).hide();
                } else {
                    jQuery('#wpms-sel-report' + slug).show();
                    clearInterval(reports.rtRuns);
                }

                jQuery('#wpms-status' + slug).html('');
                switch (period) {
                    case 'today':
                        from = 'today';
                        to = 'today';
                        break;
                    case 'yesterday':
                        from = 'yesterday';
                        to = 'yesterday';
                        break;
                    case '7daysAgo':
                        from = '7daysAgo';
                        to = 'yesterday';
                        break;
                    case '14daysAgo':
                        from = '14daysAgo';
                        to = 'yesterday';
                        break;
                    case '90daysAgo':
                        from = '90daysAgo';
                        to = 'yesterday';
                        break;
                    case '365daysAgo':
                        from = '365daysAgo';
                        to = 'yesterday';
                        break;
                    case '1095daysAgo':
                        from = '1095daysAgo';
                        to = 'yesterday';
                        break;
                    default:
                        from = '30daysAgo';
                        to = 'yesterday';
                        break;
                }

                tools.setCookie('default_metric', query);
                tools.setCookie('default_dimension', period);

                if (typeof view !== 'undefined') {
                    tools.setCookie('default_view', view);
                    projectId = view;
                } else {
                    projectId = false;
                }

                if (wpmsItemData.scope === 'admin-item') {
                    postData = {
                        action: 'wpms',
                        task: 'backend_item_reports',
                        wpms_security_backend_item_reports: wpmsItemData.security,
                        from: from,
                        to: to,
                        filter: itemId
                    }
                } else if (wpmsItemData.scope === 'front-item') {
                    postData = {
                        action: 'wpms',
                        task: 'frontend_item_reports',
                        wpms_security_frontend_item_reports: wpmsItemData.security,
                        from: from,
                        to: to,
                        filter: wpmsItemData.filter
                    }
                } else {
                    postData = {
                        action: 'wpms',
                        task: 'backend_item_reports',
                        wpms_security_backend_item_reports: wpmsItemData.security,
                        projectId: projectId,
                        from: from,
                        to: to
                    }
                }
                if (period === 'realtime') {

                    reports.i18n = wpmsItemData.i18n.slice(20, 26);

                    reports.render.focusFlag = 1;

                    jQuery(window).bind("focus", function () {
                        reports.render.focusFlag = 1;
                    }).bind("blur", function () {
                        reports.render.focusFlag = 0;
                    });

                    tpl = '<div id="wpms-realtime' + slug + '">';
                    tpl += '<div class="wpms-rt-box">';
                    tpl += '<div class="wpms-tdo-left">';
                    tpl += '<div class="wpms-online" id="wpms-online">0</div>';
                    tpl += '</div>';
                    tpl += '<div class="wpms-tdo-right" id="wpms-tdo-right">';
                    tpl += '<div class="wpms-bigtext">';
                    tpl += '<div class="wpms-bleft">' + reports.i18n[ 0 ] + '</div>';
                    tpl += '<div class="wpms-bright">0</div>';
                    tpl += '</div>';
                    tpl += '<div class="wpms-bigtext">';
                    tpl += '<div class="wpms-bleft">' + reports.i18n[ 1 ] + '</div>';
                    tpl += '<div class="wpms-bright">0</div>';
                    tpl += '</div>';
                    tpl += '<div class="wpms-bigtext">';
                    tpl += '<div class="wpms-bleft">' + reports.i18n[ 2 ] + '</div>';
                    tpl += '<div class="wpms-bright">0</div>';
                    tpl += '</div>';
                    tpl += '<div class="wpms-bigtext">';
                    tpl += '<div class="wpms-bleft">' + reports.i18n[ 3 ] + '</div>';
                    tpl += '<div class="wpms-bright">0</div>';
                    tpl += '</div>';
                    tpl += '<div class="wpms-bigtext">';
                    tpl += '<div class="wpms-bleft">' + reports.i18n[ 4 ] + '</div>';
                    tpl += '<div class="wpms-bright">0</div>';
                    tpl += '</div>';
                    tpl += '<div class="wpms-bigtext">';
                    tpl += '<div class="wpms-bleft">' + reports.i18n[ 5 ] + '</div>';
                    tpl += '<div class="wpms-bright">0</div>';
                    tpl += '</div>';
                    tpl += '</div>';
                    tpl += '</div>';
                    tpl += '<div>';
                    tpl += '<div id="wpms-pages" class="wpms-pages">&nbsp;</div>';
                    tpl += '</div>';
                    tpl += '</div>';

                    jQuery('#wpms-reports' + slug).html(tpl);

                    reports.rtRefresh(reports.render.focusFlag);

                    reports.rtRuns = setInterval(reports.rtRefresh, 55000);

                } else {
                    if (jQuery.inArray(query, ['referrers', 'contentpages', 'searches']) > -1) {

                        tpl = '<div id="wpms-orgcharttablechart' + slug + '">';
                        tpl += '<div id="wpms-orgchart' + slug + '"></div>';
                        tpl += '<div id="wpms-tablechart' + slug + '"></div>';
                        tpl += '</div>';

                        jQuery('#wpms-reports' + slug).html(tpl);
                        jQuery('#wpms-reports' + slug).hide();

                        postData.query = 'channelGrouping,' + query;

                        jQuery.post(wpmsItemData.ajaxurl, postData, function (response) {
                            reports.orgChartTableChart(response);
                        });

                    } else if (query == 'trafficdetails' || query == 'technologydetails') {

                        tpl = '<div id="wpms-orgchartpiecharts' + slug + '">';
                        tpl += '<div id="wpms-orgchart' + slug + '"></div>';
                        tpl += '<div class="wpms-floatwraper">';
                        tpl += '<div id="wpms-piechart-1' + slug + '" class="halfsize floatleft"></div>';
                        tpl += '<div id="wpms-piechart-2' + slug + '" class="halfsize floatright"></div>';
                        tpl += '</div>';
                        tpl += '<div class="wpms-floatwraper">';
                        tpl += '<div id="wpms-piechart-3' + slug + '" class="halfsize floatleft"></div>';
                        tpl += '<div id="wpms-piechart-4' + slug + '" class="halfsize floatright"></div>';
                        tpl += '</div>';
                        tpl += '</div>';

                        jQuery('#wpms-reports' + slug).html(tpl);
                        jQuery('#wpms-reports' + slug).hide();
                        if (query == 'trafficdetails') {
                            postData.query = 'channelGrouping,medium,visitorType,source,socialNetwork';
                            reports.i18n = wpmsItemData.i18n.slice(0, 5);
                        } else {
                            reports.i18n = wpmsItemData.i18n.slice(15, 20);
                            postData.query = 'deviceCategory,browser,operatingSystem,screenResolution,mobileDeviceBranding';
                        }

                        jQuery.post(wpmsItemData.ajaxurl, postData, function (response) {
                            reports.orgChartPieCharts(response)
                        });

                    } else if (query === 'locations') {

                        tpl = '<div id="wpms-geocharttablechart' + slug + '">';
                        tpl += '<div id="wpms-geochart' + slug + '"></div>';
                        tpl += '<div id="wpms-tablechart' + slug + '"></div>';
                        tpl += '</div>';

                        jQuery('#wpms-reports' + slug).html(tpl);
                        jQuery('#wpms-reports' + slug).hide();

                        postData.query = query;

                        jQuery.post(wpmsItemData.ajaxurl, postData, function (response) {
                            reports.geoChartTableChart(response);
                        });

                    } else {

                        tpl = '<div id="wpms-areachartbottomstats' + slug + '">';
                        tpl += '<div id="wpms-areachart' + slug + '"></div>';
                        tpl += '<div id="wpms-bottomstats' + slug + '">';
                        tpl += '<div class="inside">';
                        tpl += '<div class="small-box"><h3>' + wpmsItemData.i18n[ 5 ] + '</h3><p id="gdsessions' + slug + '">&nbsp;</p></div>';
                        tpl += '<div class="small-box"><h3>' + wpmsItemData.i18n[ 6 ] + '</h3><p id="gdusers' + slug + '">&nbsp;</p></div>';
                        tpl += '<div class="small-box"><h3>' + wpmsItemData.i18n[ 7 ] + '</h3><p id="gdpageviews' + slug + '">&nbsp;</p></div>';
                        tpl += '<div class="small-box"><h3>' + wpmsItemData.i18n[ 8 ] + '</h3><p id="gdbouncerate' + slug + '">&nbsp;</p></div>';
                        tpl += '<div class="small-box"><h3>' + wpmsItemData.i18n[ 9 ] + '</h3><p id="gdorganicsearch' + slug + '">&nbsp;</p></div>';
                        tpl += '<div class="small-box"><h3>' + wpmsItemData.i18n[ 10 ] + '</h3><p id="gdpagespervisit' + slug + '">&nbsp;</p></div>';
                        tpl += '</div>';
                        tpl += '</div>';
                        tpl += '</div>';

                        jQuery('#wpms-reports' + slug).html(tpl);
                        jQuery('#wpms-reports' + slug).hide();

                        postData.query = query + ',bottomstats';

                        jQuery.post(wpmsItemData.ajaxurl, postData, function (response) {
                            reports.areaChartBottomStats(response);
                        });

                    }

                }

            },
            refresh: function () {
                if (jQuery('#wpms-areachartbottomstats' + slug).length > 0 && jQuery.isArray(reports.areaChartBottomStatsData)) {
                    reports.areaChartBottomStats(reports.areaChartBottomStatsData);
                }
                if (jQuery('#wpms-orgchartpiecharts' + slug).length > 0 && jQuery.isArray(reports.orgChartPieChartsData)) {
                    reports.orgChartPieCharts(reports.orgChartPieChartsData);
                }
                if (jQuery('#wpms-geocharttablechart' + slug).length > 0 && jQuery.isArray(reports.geoChartTableChartData)) {
                    reports.geoChartTableChart(reports.geoChartTableChartData);
                }
                if (jQuery('#wpms-orgcharttablechart' + slug).length > 0 && jQuery.isArray(reports.orgChartTableChartData)) {
                    reports.orgChartTableChart(reports.orgChartTableChartData);
                }
            },
            init: function () {

                if (!jQuery("#wpms-reports" + slug).length) {
                    return;
                }

                if (jQuery("#wpms-reports" + slug).html().length) { // only when report is empty
                    return;
                }

                try {
                    NProgress.configure({
                        parent: "#wpms-progressbar" + slug,
                        showSpinner: false
                    });
                    NProgress.start();
                } catch (e) {
                    reports.alertMessage(wpmsItemData.i18n[ 0 ]);
                }

                reports.render(jQuery('#wpms-sel-view' + slug).val(), jQuery('.wpmsga_date.active').data('value'), jQuery('.wpmsga_report.active').data('value'));

                jQuery(window).resize(function () {
                    reports.refresh();
                });
            }
        };

        template.init();

        reports.init();

        jQuery('#wpms-sel-view' + slug).change(function () {
            jQuery('#wpms-reports' + slug).html('');
            reports.init();
        });

        jQuery('#wpms-sel-period' + slug).change(function () {
            jQuery('#wpms-reports' + slug).html('');
            reports.init();
        });

        jQuery('#wpms-sel-report' + slug).change(function () {
            jQuery('#wpms-reports' + slug).html('');
            reports.init();
        });
        
        jQuery('.wpmsga_date').click(function () {
            jQuery('.wpmsga_date').removeClass('button-primary active');
            jQuery(this).addClass('button-primary active');
            jQuery('#wpms-reports' + slug).html('');
            reports.init();
        });
        
        jQuery('.wpmsga_report').click(function () {
            jQuery('.wpmsga_report').removeClass('button-primary active');
            jQuery(this).addClass('button-primary active');
            jQuery('#wpms-reports' + slug).html('');
            reports.init();
        });

        if (wpmsItemData.scope !== 'admin-widgets') {
            return this.dialog({
                width: 'auto',
                maxWidth: 510,
                height: 'auto',
                modal: true,
                fluid: true,
                dialogClass: 'wpms wp-dialog',
                resizable: false,
                title: reports.getTitle(wpmsItemData.scope),
                position: {
                    my: "top",
                    at: "top+100",
                    of: window
                }
            });
        }
    }
});

jQuery(document).ready(function () {
    if (wpmsItemData.scope === 'admin-widgets') {
        jQuery('#wpms-window-1').wpmsItemReport(1);
    } else {
        jQuery(wpmsItemData.getSelector(wpmsItemData.scope)).click(function () {
            if (!jQuery("#wpms-window-" + wpmsItemData.getID(this)).length > 0) {
                jQuery("body").append('<div id="wpms-window-' + wpmsItemData.getID(this) + '"></div>');
            }
            jQuery('#wpms-window-' + wpmsItemData.getID(this)).wpmsItemReport(wpmsItemData.getID(this));
        });
    }

    // on window resize
    jQuery(window).resize(function () {
        wpmsItemData.responsiveDialog();
    });

    // dialog width larger than viewport
    jQuery(document).on("dialogopen", ".ui-dialog", function (event, ui) {
        wpmsItemData.responsiveDialog();
    });

    jQuery(document).on("click", ".wpmsClearauthor", function () {
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                'action': 'wpms',
                'task': 'ga_clearauthor'
            },
            success: function () {
                window.location.assign(wpmsItemData.admin_url+'admin.php?page=metaseo_google_analytics');
            }
        });
    });

    jQuery(document).on("click", "#wpmsga_dash_userapi", function () {
        if(jQuery(this).is(':checked')){
            var userapi = 1;
        }else{
            userapi = 0;
        }
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'ga_update_option',
                userapi: userapi
            },
            success: function () {
                window.location.assign(document.URL);
            }
        });
    });

    jQuery('.metaseo_tool').qtip({
        content: {
            attr: 'data-alt'
        },
        position: {
            my: 'bottom left',
            at: 'top left'
        },
        style: {
            tip: {
                corner: true
            },
            classes: 'metaseo-qtip qtip-rounded'
        },
        show: 'hover',
        hide: {
            fixed: true,
            delay: 10
        }

    });
});
