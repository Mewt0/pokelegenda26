<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Команда покемонов - Pokemon 8.0</title>
  <style>
    * { box-sizing: border-box; }
    html, body { width: 100%; height: 100%; }
    body {
      margin: 0;
      overflow: hidden;
      background: #dfeaf5;
      color: #102544;
      font: 14px/1.35 "Trebuchet MS", Tahoma, Arial, sans-serif;
      display: grid;
      place-items: stretch center;
    }
    button { font: inherit; cursor: pointer; }
    .team-window {
      width: min(1180px, 100vw);
      height: 100vh;
      display: grid;
      grid-template-rows: 38px minmax(0, 1fr) 24px;
      background: #e5eff8;
      border: 1px solid #8ea7bf;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.72);
      overflow: hidden;
    }
    .team-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 14px;
      color: #061a32;
      font: 800 22px/1 Georgia, "Times New Roman", serif;
      letter-spacing: .02em;
      text-transform: uppercase;
    }
    .team-head-actions { display: inline-flex; align-items: center; gap: 8px; }
    .team-icon-btn,
    .team-close {
      width: 30px;
      height: 30px;
      display: grid;
      place-items: center;
      border: 0;
      background: transparent;
      color: #526678;
      font: 400 24px/1 Arial, sans-serif;
    }
    .team-close { font-size: 30px; }
    .team-body {
      min-height: 0;
      display: grid;
      grid-template-columns: 228px minmax(0, 1fr);
      gap: 14px;
      padding: 4px 12px 2px;
    }
    .team-body.is-overview {
      grid-template-columns: 1fr;
    }
    .team-list {
      min-width: 0;
      min-height: 0;
      display: grid;
      grid-template-columns: 1fr;
      align-content: start;
      gap: 8px;
      overflow-y: auto;
      overflow-x: hidden;
      padding: 2px 4px 2px 0;
    }
    .team-list.is-overview {
      display: block;
      overflow: auto;
      padding: 2px 4px 8px;
    }
    .team-list.is-detail {
      overflow-y: auto;
      overflow-x: hidden;
    }
    .poke-tile {
      height: 104px;
      border: 1px solid #a8b8c9;
      border-radius: 6px;
      background: rgba(230, 238, 246, .84);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
      position: relative;
      overflow: hidden;
      text-align: left;
      padding: 6px 8px;
    }
    .team-list.is-overview .poke-tile {
      height: 132px;
      min-width: 0;
    }
    .team-section-active .poke-tile {
      height: 138px;
      background: linear-gradient(180deg, rgba(246,250,255,.96), rgba(225,237,248,.9));
    }
    .team-section-nursery .poke-tile {
      height: 116px;
      background: rgba(238, 244, 250, .82);
    }
    .team-list.is-detail .poke-tile {
      height: 150px;
    }
    .poke-tile.is-active { border-color: #5d8cc0; box-shadow: 0 0 0 2px rgba(72,132,209,.14) inset; }
    .poke-tile .ball { position: absolute; left: 6px; top: 9px; width: 23px; height: 23px; object-fit: contain; }
    .poke-tile .lock { position: absolute; right: 8px; top: 10px; color: #8190a0; font-size: 13px; }
    .tile-icons { position: absolute; left: 25px; top: 37px; display: grid; gap: 3px; }
    .tile-icons span {
      width: 16px;
      height: 16px;
      display: grid;
      place-items: center;
      border: 1px solid #77899a;
      border-radius: 3px;
      background: #d9f1cb;
      color: #17365e;
      font-size: 10px;
      font-weight: 800;
    }
    .tile-icons span:nth-child(2) { background: #d9c5ff; }
    .tile-art { height: 58px; margin: 0 24px 0 42px; display: grid; place-items: center; }
    .tile-art img { max-width: 116px; max-height: 66px; object-fit: contain; }
    .team-list.is-overview .tile-art,
    .team-list.is-detail .tile-art {
      height: 74px;
    }
    .team-list.is-overview .tile-icons,
    .team-list.is-overview .lock {
      display: none;
    }
    .team-list.is-overview .tile-art {
      margin: 0 18px;
    }
    .team-list.is-overview .tile-art img,
    .team-list.is-detail .tile-art img {
      max-width: 132px;
      max-height: 86px;
    }
    .team-section-active .tile-art {
      height: 82px;
    }
    .team-section-active .tile-art img {
      max-width: 146px;
      max-height: 94px;
    }
    .team-section-nursery .tile-art {
      height: 62px;
      margin-left: 38px;
    }
    .team-section-nursery .tile-art img {
      max-width: 112px;
      max-height: 72px;
    }
    .tile-lvl { margin-left: 5px; color: #071b30; font: 800 17px/1 Georgia, serif; }
    .hpbar { height: 7px; margin: 3px 0 5px; border: 1px solid #aebccc; border-radius: 999px; background: #d5e0ea; overflow: hidden; }
    .hpbar i { display: block; height: 100%; background: #0ac18f; }
    .tile-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .tile-name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #0b1726; font: 800 14px/1 Georgia, serif; text-transform: uppercase; }
    .gender { color: #6c87a4; font-size: 17px; }
    .tile-held {
      position: absolute;
      right: 8px;
      top: 34px;
      width: 26px;
      height: 26px;
      display: grid;
      place-items: center;
      border: 1px solid rgba(128,150,174,.65);
      border-radius: 6px;
      background: rgba(255,255,255,.72);
      box-shadow: 0 4px 10px rgba(37,65,99,.12);
    }
    .team-list.is-overview .tile-held { top: 8px; }
    .tile-held[hidden] { display: none; }
    .tile-held img { width: 22px; height: 22px; object-fit: contain; }
    .held-detail {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      min-height: 28px;
      margin: 6px 0 2px;
      padding: 3px 8px;
      border: 1px solid #b7c9df;
      border-radius: 6px;
      background: rgba(255,255,255,.55);
      color: #314967;
      font-weight: 800;
      cursor: pointer;
      user-select: none;
    }
    .held-detail:hover,
    .held-detail:focus-visible {
      border-color: #6d9ed2;
      background: rgba(255,255,255,.82);
      outline: none;
      box-shadow: 0 0 0 3px rgba(65,132,214,.16);
    }
    .held-detail[hidden] { display: none; }
    .held-detail img { width: 22px; height: 22px; object-fit: contain; }
    .held-confirm-pop {
      position: fixed;
      z-index: 80;
      width: min(320px, calc(100vw - 18px));
      display: grid;
      gap: 8px;
      padding: 10px;
      border: 1px solid #8da9c4;
      border-radius: 8px;
      background: linear-gradient(180deg, rgba(246,251,255,.98), rgba(219,234,248,.98));
      box-shadow: 0 14px 34px rgba(28,48,72,.22);
      color: #183453;
      font-weight: 800;
    }
    .held-confirm-pop strong { font-size: 14px; }
    .held-confirm-pop p {
      margin: 0;
      color: #4d6682;
      font-size: 12px;
      line-height: 1.35;
    }
    .held-confirm-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
    }
    .held-confirm-actions button {
      min-height: 30px;
      border: 1px solid #8fa8c2;
      border-radius: 6px;
      background: rgba(255,255,255,.74);
      color: #243d5c;
      font-weight: 900;
    }
    .held-confirm-actions .confirm {
      border-color: #62a674;
      background: #65bf82;
      color: #fff;
    }
    .empty-tile {
      min-height: 72px;
      display: grid;
      place-items: center;
      border: 1px solid #a8b8c9;
      border-radius: 6px;
      background: repeating-linear-gradient(135deg, rgba(231,239,247,.62), rgba(231,239,247,.62) 8px, rgba(217,229,241,.62) 8px, rgba(217,229,241,.62) 16px);
      color: #637589;
      font-size: 13px;
      font-weight: 800;
    }
    .team-section-active .empty-tile {
      min-height: 138px;
    }
    .team-section-nursery .empty-tile {
      min-height: 96px;
      grid-column: 1 / -1;
    }
    .team-section {
      display: block;
      min-width: 0;
      margin: 0 0 12px;
      padding: 10px;
      border: 1px solid rgba(150, 174, 198, .62);
      border-radius: 10px;
      background: rgba(255,255,255,.34);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.72);
    }
    .team-section-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin: 0 0 8px;
      padding: 0 2px 6px;
      border-bottom: 1px solid rgba(126, 151, 174, .38);
      color: #213b59;
      font-weight: 900;
    }
    .team-section-title span {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .team-section-title span::before {
      width: 22px;
      height: 22px;
      display: inline-grid;
      place-items: center;
      border: 1px solid #aac2dc;
      border-radius: 50%;
      background: linear-gradient(#fff, #dcecff);
      color: #2d70c9;
      font: 900 12px/1 Tahoma, Arial, sans-serif;
      content: "●";
    }
    .team-section-nursery .team-section-title span::before {
      color: #9c7a23;
      background: linear-gradient(#fff, #fff3cd);
    }
    .team-section-title small { color: #637589; font-weight: 800; }
    .team-section-grid {
      display: grid;
      gap: 8px;
      min-width: 0;
    }
    .team-section-active .team-section-grid {
      grid-template-columns: repeat(6, minmax(0, 1fr));
    }
    .team-section-nursery .team-section-grid {
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    .poke-tile.is-stored { background: rgba(238, 244, 250, .72); }
    .poke-tile.is-stored .tile-art { opacity: .86; }
    .tile-status {
      position: absolute;
      right: 8px;
      bottom: 28px;
      padding: 2px 7px;
      border: 1px solid #a8b8c9;
      border-radius: 999px;
      background: rgba(255,255,255,.74);
      color: #47627f;
      font-size: 11px;
      font-weight: 800;
    }
    .team-section-nursery .tile-status { display: none; }
    .poke-tile.is-stored .tile-status { color: #87682f; border-color: #c9b486; background: #fff8df; }
    .detail-actions {
      margin: 12px 0 0;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .team-action {
      min-height: 34px;
      padding: 0 12px;
      border: 1px solid #9fb0c0;
      border-radius: 5px;
      background: #edf5fc;
      color: #183c62;
      font-weight: 800;
    }
    .team-action.primary {
      border-color: #5f95ce;
      background: #3f85d9;
      color: #fff;
    }
    .team-action:disabled {
      opacity: .55;
      cursor: not-allowed;
    }
    .pager { text-align: center; color: #2a7dcc; font-size: 18px; letter-spacing: 5px; }
    .detail {
      min-width: 0;
      min-height: 0;
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      position: relative;
      overflow-y: auto;
      overflow-x: hidden;
      padding-right: 4px;
    }
    .team-body.is-overview .detail {
      display: none;
    }
    .detail-left { min-width: 0; display: grid; grid-template-rows: 142px auto; gap: 6px; }
    .detail-art { display: grid; place-items: center; cursor: pointer; border-radius: 6px; }
    .detail-art:hover { background: rgba(255,255,255,.34); }
    .detail-art img { max-width: 198px; max-height: 142px; object-fit: contain; }
    .move-list { min-width: 0; display: grid; gap: 5px; }
    .move-slot {
      height: 50px;
      display: grid;
      grid-template-columns: 42px minmax(0, 1fr);
      align-items: center;
      gap: 7px;
      border: 1px solid #a8b8c9;
      border-radius: 6px;
      background: rgba(231,239,247,.9);
      padding: 4px;
      text-align: left;
    }
    .move-slot.is-empty { opacity: .72; }
    .move-icon {
      width: 38px;
      height: 38px;
      display: grid;
      place-items: center;
      border: 1px solid rgba(20, 28, 36, .46);
      border-radius: 4px;
      background: linear-gradient(145deg, #d7d7d7, #a9a9a9);
      color: #1e2430;
      font-weight: 900;
      font-size: 22px;
      line-height: 1;
      text-shadow: 0 1px 0 rgba(255,255,255,.38);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.68), inset 0 -2px 0 rgba(0,0,0,.18), 0 1px 1px rgba(0,0,0,.14);
    }
    .move-icon img {
      width: 100%;
      height: 100%;
      display: block;
      object-fit: contain;
    }
    .type-normal { background: linear-gradient(145deg, #d6c39b, #9f8354); color: #5a3d1d; }
    .type-fire { background: linear-gradient(145deg, #ff9b42, #c44720); color: #6f190c; }
    .type-water { background: linear-gradient(145deg, #4bc9ef, #1688bd); color: #063c67; }
    .type-grass { background: linear-gradient(145deg, #8be468, #37a53d); color: #134e20; }
    .type-electric { background: linear-gradient(145deg, #fff153, #e7b900); color: #7a4c00; }
    .type-ice { background: linear-gradient(145deg, #b9f2ff, #64cce9); color: #1c6680; }
    .type-fighting { background: linear-gradient(145deg, #d7745c, #a63d35); color: #5f1a17; }
    .type-poison { background: linear-gradient(145deg, #d785ff, #9049c8); color: #4f1a79; }
    .type-ground { background: linear-gradient(145deg, #d2ad65, #8b622c); color: #4e3516; }
    .type-flying { background: linear-gradient(145deg, #93d7ff, #5a98d6); color: #174c84; }
    .type-psychic { background: linear-gradient(145deg, #ff83c6, #cf3b86); color: #7c1648; }
    .type-bug { background: linear-gradient(145deg, #d8df45, #91a721); color: #47560f; }
    .type-rock { background: linear-gradient(145deg, #c5b08c, #7e6a4c); color: #463824; }
    .type-ghost { background: linear-gradient(145deg, #7760b5, #3d2d70); color: #1e1645; }
    .type-dragon { background: linear-gradient(145deg, #b46eff, #6941bf); color: #361d7d; }
    .type-dark { background: linear-gradient(145deg, #6d6570, #29282d); color: #08090c; }
    .type-steel { background: linear-gradient(145deg, #c9d2dc, #8a99a7); color: #334250; }
    .type-fairy { background: linear-gradient(145deg, #ff9bd3, #db5d9f); color: #7d2455; }
    .move-main { min-width: 0; display: grid; gap: 4px; position: relative; }
    .move-name { min-width: 0; padding-right: 28px; color: #aa1616; font: 800 14px/1 Georgia, serif; text-transform: uppercase; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .move-pp { position: absolute; right: 0; top: 0; color: #526173; font-weight: 700; }
    .move-hint { color: #526173; font-size: 11px; }
    .detail-main { min-width: 0; padding: 0 2px 10px 0; }
    .detail-title { margin: 0; color: #102544; font: 800 22px/1.04 Georgia, "Times New Roman", serif; text-transform: uppercase; overflow-wrap: anywhere; }
    .detail-title[role="button"] { cursor: pointer; }
    .detail-title[role="button"]:hover { color: #0d5ca8; }
    .detail-sub { margin-top: 3px; color: #123965; font-size: 14px; overflow-wrap: anywhere; }
    .badge {
      display: inline-flex;
      align-items: center;
      min-height: 22px;
      margin-top: 4px;
      padding: 0 8px;
      border: 1px solid #9fb0c0;
      border-radius: 4px;
      background: #eaf2fb;
      color: #183c62;
      font-weight: 700;
    }
    .stats { max-width: 100%; margin-top: 10px; display: grid; gap: 4px; }
    .vitamin-summary {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
      color: #31516f;
      font-size: 11px;
      font-weight: 800;
    }
    .vitamin-summary span {
      min-height: 20px;
      display: inline-flex;
      align-items: center;
      padding: 0 7px;
      border: 1px solid #b5c7da;
      border-radius: 999px;
      background: rgba(247,251,255,.75);
      white-space: nowrap;
    }
    .vitamin-summary b { color: #0b8f58; }
    .stat-row { display: grid; grid-template-columns: 132px 42px minmax(92px, 1fr) auto auto 24px; align-items: center; gap: 7px; color: #1f3855; }
    .stat-label { min-width: 0; display: flex; align-items: center; gap: 6px; }
    .stat-label span:first-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .stat-value { text-align: right; color: #526173; font-weight: 700; }
    .stat-track { height: 10px; border: 1px solid #aab8c6; border-radius: 99px; background: #edf3f9; overflow: hidden; }
    .stat-track i { display: block; height: 100%; background: #d0c96a; }
    .stat-row.hp .stat-track i { background: #14bf91; }
    .stat-row.is-trained { color: #102544; font-weight: 800; }
    .stat-row.is-trained .stat-track { border-color: #8fb4e4; background: #e8f2ff; box-shadow: 0 0 0 2px rgba(77,139,234,.09); }
    .stat-row.is-trained .stat-track i { background: linear-gradient(90deg, #5b8df7, #8edb6f); }
    .training-mark {
      flex: 0 0 auto;
      min-width: 24px;
      height: 18px;
      display: inline-grid;
      place-items: center;
      border: 1px solid #8ca3ba;
      border-radius: 4px;
      background: linear-gradient(#fff, #d9e5ef);
      color: #28445f;
      font: 900 10px/1 Tahoma, Arial, sans-serif;
      box-shadow: 0 1px 0 rgba(255,255,255,.75) inset;
    }
    .training-mark.s1 { border-color:#9ba6ad; color:#4d5962; }
    .training-mark.s2 { border-color:#87af4b; color:#3f6c10; background:linear-gradient(#fbfff5,#dff0ca); }
    .training-mark.s3 { border-color:#5595d8; color:#145ea6; background:linear-gradient(#f5fbff,#d6e9ff); }
    .training-mark.s4 { border-color:#a06bc6; color:#6d298b; background:linear-gradient(#fff8ff,#ecd9fa); }
    .training-mark.s5 { border-color:#c95244; color:#9a1c13; background:linear-gradient(#fff8f5,#fad8d3); }
    .training-mark.s6 { border-color:#c5a21a; color:#755900; background:linear-gradient(#fffbe4,#f4db65); }
    .stat-plus {
      width: 22px;
      height: 22px;
      border: 0;
      background: transparent;
      color: #00b86b;
      font: 800 20px/1 Arial, sans-serif;
    }
    .stat-plus:disabled { opacity: .35; cursor: default; }
    .stat-ev-chip,
    .stat-vitamin-chip {
      min-height: 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      font: 900 11px/1 Tahoma, Arial, sans-serif;
      white-space: nowrap;
    }
    .stat-ev-chip {
      min-width: 82px;
      padding: 0 7px;
      border: 1px solid #a9bed4;
      background: rgba(255,255,255,.62);
      color: #31516f;
    }
    .stat-ev-chip.is-full {
      border-color: #a9d7bd;
      background: #e4f8ed;
      color: #0b8f58;
    }
    .stat-vitamin-chip {
      min-width: 34px;
      border: 1px solid #77a7d8;
      background: #eef7ff;
      color: #1d66a8;
    }
    .stat-vitamin-chip.is-empty {
      border-color: #c1cbd5;
      background: #edf1f5;
      color: #7b8793;
    }
    .ev-left { justify-self: end; color: #00a75f; font-weight: 900; }
    .meta { margin-top: 18px; color: #0d5ca8; font-weight: 700; }
    .training-box {
      margin-top: 10px;
      padding: 8px 10px;
      border: 1px solid #b3c5d8;
      border-radius: 6px;
      background: rgba(255,255,255,.42);
    }
    .training-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:4px; }
    .training-box strong { display:block; color:#102544; }
    .training-box small { display:block; color:#526173; line-height: 1.25; }
    .training-chances { margin-top:5px; display:flex; flex-wrap:wrap; gap:4px; }
    .training-chances span {
      min-height: 18px;
      display: inline-flex;
      align-items: center;
      padding: 0 6px;
      border: 1px solid #b8c8d8;
      border-radius: 999px;
      background: rgba(247,251,255,.72);
      color: #435b74;
      font-size: 10px;
      font-weight: 800;
    }
    .training-actions { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
    .training-actions button {
      min-height: 26px;
      padding: 0 9px;
      border: 1px solid #9fb4cc;
      border-radius: 5px;
      background: #f7fbff;
      color: #14395f;
      font-weight: 800;
    }
    .training-actions button:hover { border-color:#4d8bea; color:#0d5ca8; }
    .starter {
      float: right;
      min-height: 26px;
      padding: 0 10px;
      border: 0;
      border-radius: 5px;
      background: #91af13;
      color: #fff;
      font-weight: 800;
    }
    .learn-pop,
    .ev-pop {
      position: fixed;
      z-index: 40;
      border-radius: 5px;
      box-shadow: 0 8px 22px rgba(20,36,54,.28);
    }
    .learn-pop {
      width: 276px;
      max-height: min(386px, calc(100vh - 18px));
      overflow: hidden;
      padding: 7px;
      border: 1px solid #9fb8d2;
      background: linear-gradient(#edf6ff, #dcebf8);
      color: #102544;
    }
    .learn-pop h3 {
      margin: 0 0 6px;
      padding-bottom: 5px;
      border-bottom: 1px solid rgba(105, 137, 171, .38);
      color: #173a61;
      font: 900 14px/1.1 Tahoma, Arial, sans-serif;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: .2px;
    }
    .learn-search {
      width: 100%;
      height: 27px;
      margin: 0 0 5px;
      padding: 0 8px;
      border: 1px solid #a9bed4;
      border-radius: 4px;
      background: #f8fbff;
      color: #102544;
      outline: none;
      font-size: 12px;
    }
    .learn-search:focus { border-color: #7397d3; box-shadow: 0 0 0 2px rgba(77,139,234,.18); }
    .learn-results {
      max-height: 318px;
      display: grid;
      gap: 4px;
      overflow: auto;
      padding-right: 2px;
    }
    .learn-row {
      width: 100%;
      height: 27px;
      display: grid;
      grid-template-columns: 22px minmax(0, 1fr) auto;
      gap: 6px;
      align-items: center;
      border: 1px solid #afc3d8;
      border-radius: 4px;
      background: rgba(243, 248, 253, .95);
      text-align: left;
      padding: 0 7px;
      color: #173a61;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
      transition: background .12s ease, border-color .12s ease, transform .12s ease;
    }
    .learn-row:hover,
    .learn-row:focus {
      border-color: #6fa0d8;
      background: #e5f1ff;
      transform: translateY(-1px);
      outline: none;
    }
    .learn-row.type-fire,
    .learn-row.type-fighting,
    .learn-row.type-poison { background: linear-gradient(90deg, rgba(255,240,240,.92), rgba(243,248,253,.96) 42%); }
    .learn-row.type-water,
    .learn-row.type-electric,
    .learn-row.type-ice { background: linear-gradient(90deg, rgba(230,243,255,.94), rgba(243,248,253,.96) 42%); }
    .learn-row.type-ghost,
    .learn-row.type-psychic,
    .learn-row.type-dragon,
    .learn-row.type-dark { background: linear-gradient(90deg, rgba(241,235,250,.92), rgba(243,248,253,.96) 42%); }
    .learn-type-icon {
      width: 21px;
      height: 21px;
      display: grid;
      place-items: center;
      border: 1px solid rgba(30, 36, 48, .42);
      border-radius: 4px;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.58), inset 0 -1px 0 rgba(0,0,0,.13);
    }
    .learn-type-icon img {
      width: 18px;
      height: 18px;
      display: block;
      object-fit: contain;
    }
    .learn-row strong {
      min-width: 0;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
      color: #173a61;
      font: 900 12px/1 Tahoma, Arial, sans-serif;
      text-transform: uppercase;
    }
    .learn-row small {
      color: #4f6b88;
      font: 800 11px/1 Tahoma, Arial, sans-serif;
      white-space: nowrap;
    }
    .learn-empty { color: #515b65; padding: 8px 2px; font-size: 12px; }
    .ev-pop {
      width: 264px;
      padding: 10px 12px 12px;
      background: rgba(12,14,18,.94);
      color: #fff;
      text-align: center;
    }
    .ev-pop strong { display: block; margin-bottom: 8px; }
    .ev-pop-line { margin: 5px 0; color: #d8e2ef; font-size: 12px; text-align: left; }
    .ev-pop label { display: grid; grid-template-columns: 1fr 90px; align-items: center; gap: 8px; }
    .ev-pop input { min-width: 0; height: 28px; border: 1px solid #888; background: #4a4f56; color: #cad2dc; text-align: right; font: inherit; }
    .ev-pop button {
      width: 100%;
      height: 24px;
      margin-top: 8px;
      border: 1px solid #188bd6;
      border-radius: 3px;
      background: #1478bd;
      color: #fff;
      font-weight: 800;
    }
    .team-foot { display: flex; align-items: center; justify-content: space-between; padding: 0 14px 5px; color: #8190a0; font-weight: 700; }
    .status { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .is-bad { color: #a4161a; }
    @media (max-width: 760px) {
      body { overflow: auto; }
      .team-window { height: auto; min-height: 100vh; overflow: visible; }
      .team-body { grid-template-columns: 1fr; padding: 8px 10px; }
      .team-list { grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); max-height: 250px; }
      .team-list.is-overview { max-height: none; }
      .team-section-active .team-section-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .team-section-nursery .team-section-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .detail { grid-template-columns: 1fr; }
      .detail-left { grid-template-rows: auto auto; }
      .detail-art { display: none; }
    }
    @media (min-width: 761px) and (max-width: 940px) {
      .team-section-active .team-section-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
  </style>
</head>
<body data-csrf="<?= View::e($csrf) ?>">
  <main class="team-window">
    <header class="team-head">
      <span>Команда покемонов</span>
      <span class="team-head-actions">
        <button type="button" class="team-icon-btn" id="downloadBtn" aria-label="Скачать">⇩</button>
        <button type="button" class="team-close" id="closeBtn" aria-label="Закрыть">×</button>
      </span>
    </header>
    <section class="team-body">
      <aside class="team-list" id="pokemonList"></aside>
      <section class="detail" id="pokemonDetail" aria-live="polite"></section>
    </section>
    <footer class="team-foot">
      <span class="status" id="status">Загрузка...</span>
      <span id="detailId"></span>
    </footer>
  </main>

  <script>
    const csrf = document.body.dataset.csrf;
    const teamBody = document.querySelector('.team-body');
    const list = document.getElementById('pokemonList');
    const detail = document.getElementById('pokemonDetail');
    const status = document.getElementById('status');
    const detailId = document.getElementById('detailId');
    const slots = ['a', 'b', 'c', 'd'];
    const dragState = { active: false, offsetX: 0, offsetY: 0 };
    let team = [];
    let selectedId = 0;
    let detailOpen = false;
    let activeLearn = null;

    document.getElementById('closeBtn').addEventListener('click', () => {
      resetLocalState();
      if (window.parent && window.parent !== window) {
        const close = window.parent.document.getElementById('pokemonCloseBtn');
        if (close) close.click();
      } else {
        window.location.href = '/game';
      }
    });
    setupParentWindowDrag();
    document.getElementById('downloadBtn').addEventListener('click', () => setStatus('Экспорт карточки будет подключен позже.'));
    document.addEventListener('click', event => {
      if (!event.target.closest('.learn-pop,.move-slot,.ev-pop,.stat-plus,.held-confirm-pop,.held-detail')) closePopups();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') closePopups();
    });

    function setStatus(text, bad = false) {
      status.textContent = text;
      status.classList.toggle('is-bad', bad);
    }

    function setupParentWindowDrag() {
      const head = document.querySelector('.team-head');
      if (!head || !window.parent || window.parent === window) return;
      head.style.cursor = 'move';

      const parentDoc = window.parent.document;
      const parentWin = window.parent.window;
      const clamp = (left, top, win) => {
        const pad = 8;
        const maxLeft = Math.max(pad, parentWin.innerWidth - win.offsetWidth - pad);
        const maxTop = Math.max(pad, parentWin.innerHeight - win.offsetHeight - pad);
        return {
          left: Math.max(pad, Math.min(left, maxLeft)),
          top: Math.max(pad, Math.min(top, maxTop)),
        };
      };

      const parentPoint = event => {
        const frame = parentDoc.getElementById('pokemonFrame');
        const rect = frame ? frame.getBoundingClientRect() : { left: 0, top: 0 };
        return { x: rect.left + event.clientX, y: rect.top + event.clientY };
      };

      head.addEventListener('pointerdown', event => {
        if (event.target.closest('button')) return;
        const win = parentDoc.querySelector('.pokemon-window');
        if (!win) return;
        const point = parentPoint(event);
        const rect = win.getBoundingClientRect();
        dragState.active = true;
        dragState.offsetX = point.x - rect.left;
        dragState.offsetY = point.y - rect.top;
        win.classList.add('is-dragged');
        win.style.left = rect.left + 'px';
        win.style.top = rect.top + 'px';
        try { head.setPointerCapture(event.pointerId); } catch (e) {}
      });

      head.addEventListener('pointermove', event => {
        if (!dragState.active) return;
        const win = parentDoc.querySelector('.pokemon-window');
        if (!win) return;
        const point = parentPoint(event);
        const pos = clamp(point.x - dragState.offsetX, point.y - dragState.offsetY, win);
        win.style.left = pos.left + 'px';
        win.style.top = pos.top + 'px';
      });

      const stopDrag = event => {
        dragState.active = false;
        try { head.releasePointerCapture(event.pointerId); } catch (e) {}
      };
      head.addEventListener('pointerup', stopDrag);
      head.addEventListener('pointercancel', stopDrag);
    }

    async function load(keepSelected = true) {
      try {
        const response = await fetch('/api/pokemon/moves', { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          setStatus('Не удалось загрузить покемонов.', true);
          return;
        }
        team = payload.pokemon || [];
        if (!keepSelected) {
          detailOpen = false;
          selectedId = 0;
        } else if (!team.some(poke => Number(poke.id) === Number(selectedId))) {
          selectedId = 0;
          detailOpen = false;
        }
        renderAll();
        setStatus('Готово');
      } catch (error) {
        setStatus('Не удалось загрузить покемонов.', true);
      }
    }

    function renderAll() {
      renderTeamView();
      return;
      list.innerHTML = '';
      if (!team.length) {
        list.innerHTML = '<div class="empty-tile">- пусто -</div>';
        detail.innerHTML = '';
        return;
      }
      team.forEach(poke => list.appendChild(renderTile(poke)));
      const emptyCount = Math.max(0, 6 - team.length);
      for (let i = 0; i < emptyCount; i++) {
        const empty = document.createElement('div');
        empty.className = 'empty-tile';
        empty.textContent = '- пусто -';
        list.appendChild(empty);
      }
      const pager = document.createElement('div');
      pager.className = 'pager';
      pager.textContent = '...';
      list.appendChild(pager);
      renderDetail(selectedPokemon());
    }

    function appendEmptyTile(target, text = 'Свободный слот') {
      const empty = document.createElement('div');
      empty.className = 'empty-tile';
      empty.textContent = text;
      target.appendChild(empty);
    }

    function appendPager(target) {
      const pager = document.createElement('div');
      pager.className = 'pager';
      pager.textContent = '...';
      target.appendChild(pager);
    }

    function renderTeamView() {
      closePopups();
      teamBody.classList.toggle('is-overview', !detailOpen);
      list.innerHTML = '';

      if (!team.length) {
        list.className = 'team-list is-overview';
        appendEmptyTile(list, 'Покемонов нет');
        detail.innerHTML = '';
        detailId.textContent = '';
        return;
      }

      if (!detailOpen) {
        list.className = 'team-list is-overview';
        renderPokemonSection('Активная команда', activePokemon(), 'active', 'Команда пуста.');
        detail.innerHTML = '';
        detailId.textContent = '';
        return;
      }

      const selected = selectedPokemon();
      list.className = 'team-list is-detail';
      list.appendChild(renderTile(selected));
      const moves = document.createElement('div');
      moves.className = 'move-list';
      slots.forEach((slot, index) => moves.appendChild(renderMoveSlot(selected, slot, index)));
      list.appendChild(moves);
      appendPager(list);
      renderPokemonDetail(selected);
    }

    function activePokemon() {
      return team.filter(poke => poke.active === true);
    }

    function nurseryPokemon() {
      return team.filter(poke => poke.active === false);
    }

    function renderPokemonSection(title, pokemons, kind, emptyText) {
      const section = document.createElement('section');
      section.className = 'team-section team-section-' + kind;
      const head = document.createElement('div');
      head.className = 'team-section-title';
      const max = kind === 'active' ? '/6' : '';
      head.innerHTML = '<span></span><small></small>';
      head.querySelector('span').textContent = title;
      head.querySelector('small').textContent = kind === 'active'
        ? String(pokemons.length) + max + ' с собой'
        : String(pokemons.length) + ' в питомнике';
      section.appendChild(head);

      const grid = document.createElement('div');
      grid.className = 'team-section-grid';
      section.appendChild(grid);

      if (!pokemons.length) {
        const empty = document.createElement('div');
        empty.className = 'empty-tile';
        empty.textContent = emptyText;
        grid.appendChild(empty);
      } else {
        pokemons.forEach(poke => grid.appendChild(renderTile(poke)));
      }

      if (kind === 'active') {
        for (let i = pokemons.length; i < 6; i++) appendEmptyTile(grid);
      }

      list.appendChild(section);
    }

    function renderTilesOnly() {
      const scrollTop = list.scrollTop;
      list.querySelectorAll('.poke-tile').forEach(tile => {
        tile.classList.toggle('is-active', Number(tile.dataset.id) === Number(selectedId));
      });
      list.scrollTop = scrollTop;
    }

    function renderTile(poke) {
      const tile = document.createElement('button');
      tile.type = 'button';
      tile.className = 'poke-tile' + (poke.active === false ? ' is-stored' : ' is-team') + (Number(poke.id) === Number(selectedId) ? ' is-active' : '');
      tile.dataset.id = String(poke.id);
      tile.addEventListener('click', () => {
        if (detailOpen && Number(poke.id) === Number(selectedId)) {
          openDexPokemon(poke);
          return;
        }
        selectedId = Number(poke.id);
        detailOpen = true;
        renderAll();
      });
      tile.innerHTML = [
        '<img class="ball" src="/public/img/legacy/ui/menu-pokemon.png" alt="">',
        '<span class="lock" aria-hidden="true">▣</span>',
        '<span class="tile-held" hidden><img alt=""></span>',
        '<div class="tile-icons"><span>ST</span><span>✦</span></div>',
        '<span class="tile-status"></span>',
        '<div class="tile-art"><img alt=""></div>',
        '<div class="tile-lvl"></div>',
        '<div class="hpbar"><i></i></div>',
        '<div class="tile-foot"><span class="tile-name"></span><span class="gender">♂</span></div>',
      ].join('');
      setSprite(tile.querySelector('.tile-art img'), poke);
      tile.querySelector('.tile-lvl').textContent = Number(poke.level || 0);
      tile.querySelector('.hpbar i').style.width = hpPercent(poke) + '%';
      tile.querySelector('.tile-name').textContent = displayName(poke);
      tile.querySelector('.tile-status').textContent = poke.active === false ? 'Питомник' : (poke.starter ? 'Стартовый' : 'Команда');
      renderHeldBadge(tile.querySelector('.tile-held'), poke);
      if (!poke.starter) tile.querySelector('.tile-icons span').textContent = '★';
      return tile;
    }

    function renderPokemonDetail(poke) {
      if (!poke) return;
      detailId.textContent = 'id' + Number(poke.id || 0);
      detail.innerHTML = [
        '<div class="detail-main">',
        '<h2 class="detail-title"></h2>',
        '<div class="detail-sub"></div>',
        '<span class="badge">Обычный характер</span>',
        '<div class="held-detail" hidden><img alt=""><span></span></div>',
        '<div class="stats"></div>',
        '<div class="training-box"></div>',
        '<div class="detail-actions"></div>',
        '<div class="meta">☻ ВаДИлаа 2 дня назад <button type="button" class="starter">стартовый</button></div>',
        '</div>'
      ].join('');
      detail.querySelector('.detail-title').textContent = displayName(poke);
      bindDexOpen(detail.querySelector('.detail-title'), poke);
      detail.querySelector('.detail-sub').textContent = '#' + pad3(displayDexNumber(poke)) + ' ' + displayName(poke) + '  ♂';
      renderHeldDetail(detail.querySelector('.held-detail'), poke);
      detail.querySelector('.starter').hidden = !poke.starter;
      renderStats(poke);
      renderTraining(poke);
      renderNurseryActions(poke);
    }

    function renderDetail(poke) {
      closePopups();
      if (!poke) return;
      detailId.textContent = 'id' + Number(poke.id || 0);
      detail.innerHTML = [
        '<div class="detail-left"><div class="detail-art"><img alt=""></div><div class="move-list"></div></div>',
        '<div class="detail-main">',
        '<h2 class="detail-title"></h2>',
        '<div class="detail-sub"></div>',
        '<span class="badge">Обычный характер</span>',
        '<div class="held-detail" hidden><img alt=""><span></span></div>',
        '<div class="stats"></div>',
        '<div class="training-box"></div>',
        '<div class="detail-actions"></div>',
        '<div class="meta">☻ ВадИлаа 2 дня назад <button type="button" class="starter">стартовый</button></div>',
        '</div>'
      ].join('');
      setSprite(detail.querySelector('.detail-art img'), poke);
      bindDexOpen(detail.querySelector('.detail-art'), poke);
      detail.querySelector('.detail-title').textContent = displayName(poke);
      bindDexOpen(detail.querySelector('.detail-title'), poke);
      detail.querySelector('.detail-sub').textContent = '#' + pad3(displayDexNumber(poke)) + ' ' + displayName(poke) + '  ♂';
      renderHeldDetail(detail.querySelector('.held-detail'), poke);
      detail.querySelector('.starter').hidden = !poke.starter;
      slots.forEach((slot, index) => detail.querySelector('.move-list').appendChild(renderMoveSlot(poke, slot, index)));
      renderStats(poke);
      renderTraining(poke);
      renderNurseryActions(poke);
    }

    function renderNurseryActions(poke) {
      const box = detail.querySelector('.detail-actions');
      if (!box || !poke) return;
      const activeCount = activePokemon().length;
      const isStored = poke.active === false;
      const action = isStored ? 'take' : 'store';
      const disabled = isStored ? activeCount >= 6 : activeCount <= 1 || !!poke.starter;
      const hint = isStored
        ? 'В команду'
        : (poke.starter ? 'Стартового нельзя убрать' : 'В питомник');
      box.innerHTML = [
        '<button type="button" class="team-action" data-team-back>← Все покемоны</button>',
        '<button type="button" class="team-action primary" data-nursery-action="' + action + '"' + (disabled ? ' disabled' : '') + '></button>',
        '<button type="button" class="team-action" data-commission-sell>Продать</button>',
      ].join('');
      box.querySelector('[data-team-back]').addEventListener('click', event => {
        event.stopPropagation();
        selectedId = 0;
        detailOpen = false;
        renderAll();
      });
      const button = box.querySelector('[data-nursery-action]');
      button.textContent = hint;
      button.addEventListener('click', event => {
        event.stopPropagation();
        nurseryAction(Number(poke.id || 0), action);
      });
      box.querySelector('[data-commission-sell]')?.addEventListener('click', event => {
        event.stopPropagation();
        window.location.href = '/game/commission';
      });
    }

    function renderTraining(poke) {
      const box = detail.querySelector('.training-box');
      if (!box || !poke) return;
      const t = poke.training || {};
      const stage = Number(t.stage || 0);
      const named = t.namedEffect ? ', эффект: ' + namedEffectLabel(t.namedEffect) : '';
      const mark = trainingMarkHtml(t);
      box.innerHTML = [
        '<div class="training-head"><strong>Тренировка: ' + escapeHtml(t.stageName || 'Без тренировки') + '</strong>' + mark + '</div>',
        '<small>Бонус: +' + Number(t.bonus || 0) + '% к ' + escapeHtml(t.statLabel || 'Не выбран') + named + '</small>',
        '<small>' + (t.tamed ? 'Приручен: нельзя передавать.' : 'Не приручен.') + '</small>',
        '<div class="training-chances">',
          '<span>Успех: ' + formatChance(t.successChance) + '</span>',
          '<span>С умением: ' + formatChance(t.boostedSuccessChance) + '</span>',
          '<span>Ослабление: ' + formatChance(t.weakenChance) + '</span>',
        '</div>',
        '<div class="training-actions">',
          '<button type="button" data-training-action="train">Набор тренировки</button>',
          '<button type="button" data-training-action="weaken"' + (stage <= 0 ? ' disabled' : '') + '>Ослабить</button>',
        '</div>',
      ].join('');
      box.querySelectorAll('[data-training-action]').forEach(button => {
        button.addEventListener('click', event => {
          event.stopPropagation();
          useTrainingItem(Number(poke.id || 0), button.dataset.trainingAction || 'train');
        });
      });
    }

    function trainingMarkHtml(training) {
      const stage = Number(training && training.stage || 0);
      if (stage <= 0) return '';
      const label = escapeHtml(training.icon || String(stage));
      const title = escapeHtml((training.stageName || 'Тренировка') + ': +' + Number(training.bonus || 0) + '%');
      return '<span class="training-mark s' + stage + '" title="' + title + '">' + label + '</span>';
    }

    function trainingStatKey(label) {
      return {
        'Атака': 'atk',
        'Защита': 'def',
        'Скорость': 'speed',
        'Спец.атака': 'satk',
        'Спец.защита': 'sdef',
      }[label] || '';
    }

    function formatChance(value) {
      const num = Number(value || 0);
      return (Number.isInteger(num) ? String(num) : String(num).replace('.', ',')) + '%';
    }

    function namedEffectLabel(effect) {
      return {
        burn: 'ожог',
        paralyze: 'паралич',
        freeze: 'заморозка',
        poison: 'яд',
        confuse: 'спутанность',
        fear: 'страх',
      }[String(effect || '')] || effect;
    }

    function renderMoveSlot(poke, slot, index) {
      const current = poke.moves && poke.moves[slot] ? poke.moves[slot] : { id: 0, name: 'Нет атаки' };
      const type = current.type || 'Normal';
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'move-slot' + (Number(current.id || 0) <= 0 ? ' is-empty' : '');
      row.innerHTML = [
        '<span class="move-icon"></span>',
        '<span class="move-main"><span class="move-name"></span><span class="move-pp"></span><span class="move-hint"></span></span>'
      ].join('');
      const icon = row.querySelector('.move-icon');
      icon.classList.add(typeClass(type));
      icon.innerHTML = '<img alt="">';
      const img = icon.querySelector('img');
      img.src = typeIconSrc(type);
      img.alt = typeLabel(type);
      icon.title = typeLabel(type);
      row.querySelector('.move-name').textContent = current.name || 'Нет атаки';
      row.querySelector('.move-pp').textContent = current.id ? ppText(current) : '';
      row.querySelector('.move-hint').textContent = current.id ? 'Нажмите, чтобы заменить' : 'Изучить атаку';
      row.addEventListener('click', event => {
        event.stopPropagation();
        showLearnPopup(row, poke, slot, current);
      });
      return row;
    }

    function renderStats(poke) {
      const level = Number(poke.level || 0);
      const hp = Number(poke.hp || 0);
      const hpMax = Number(poke.hpMax || 1);
      const training = poke.training || {};
      const trainedStat = String(training.stat || '');
      const stats = poke.stats || {};
      const ev = poke.ev || {};
      const vitaminCapacity = poke.vitaminCapacity || {};
      const vitaminByStat = vitaminCapacity.byStat || {};
      const evMaxPerStat = Math.max(1, Number(ev.maxPerStat || 252));
      const evMaxTotal = Math.max(1, Number(ev.maxTotal || 510));
      const evTotal = Math.max(0, Number(ev.total || 0));
      const evRemainingTotal = Math.max(0, Number(ev.remainingTotal || Math.max(0, evMaxTotal - evTotal)));
      const vitaminUsesLeft = Math.max(0, Number(vitaminCapacity.remainingTotalUses || 0));
      const values = [
        { label: 'Счастье', key: '', value: Number(poke.happiness ?? 100), max: 100, isHp: false, hasEv: false },
        { label: 'Здоровье', key: 'hp', value: Number(stats.hp || hpMax || hp), max: Math.max(1, Number(stats.hp || hpMax || hp)), isHp: true, hasEv: true },
        { label: 'Атака', key: 'atk', value: Number(stats.atk || Math.max(1, Math.round(level * 0.58))), max: 100, isHp: false, hasEv: true },
        { label: 'Защита', key: 'def', value: Number(stats.def || Math.max(1, Math.round(level * 0.58))), max: 100, isHp: false, hasEv: true },
        { label: 'Скорость', key: 'speed', value: Number(stats.speed || Math.max(1, Math.round(level * 0.55))), max: 100, isHp: false, hasEv: true },
        { label: 'Спец.атака', key: 'satk', value: Number(stats.satk || Math.max(1, Math.round(level * 0.64))), max: 100, isHp: false, hasEv: true },
        { label: 'Спец.защита', key: 'sdef', value: Number(stats.sdef || Math.max(1, Math.round(level * 0.64))), max: 100, isHp: false, hasEv: true },
      ];
      const box = detail.querySelector('.stats');
      const summary = document.createElement('div');
      summary.className = 'vitamin-summary';
      summary.innerHTML = '<span>EV всего: <b></b></span><span>Можно влить ещё: <b></b></span><span>Доступно витаминов: <b></b></span>';
      summary.querySelectorAll('b')[0].textContent = evTotal + '/' + evMaxTotal;
      summary.querySelectorAll('b')[1].textContent = evRemainingTotal + ' EV';
      summary.querySelectorAll('b')[2].textContent = '×' + vitaminUsesLeft;
      box.appendChild(summary);
      values.forEach(item => {
        const label = item.label;
        const value = item.value;
        const max = item.max;
        const isHp = item.isHp;
        const evValue = item.hasEv ? Math.max(0, Number(ev[item.key] || 0)) : 0;
        const evRemaining = item.hasEv ? Math.max(0, Math.min(evMaxPerStat - evValue, evRemainingTotal)) : 0;
        const statVitaminLeft = item.hasEv ? Math.max(0, Number(vitaminByStat[item.key] || Math.floor(evRemaining / Math.max(1, Number(vitaminCapacity.step || 10))))) : 0;
        const isTrained = trainedStat !== '' && trainingStatKey(label) === trainedStat && Number(training.stage || 0) > 0;
        const row = document.createElement('div');
        row.className = 'stat-row' + (isHp ? ' hp' : '') + (isTrained ? ' is-trained' : '');
        row.innerHTML = '<span class="stat-label"><span></span></span><b class="stat-value"></b><span class="stat-track"><i></i></span><span class="stat-ev-chip"></span><button type="button" class="stat-plus">+</button>';
        row.querySelector('.stat-label span').textContent = label;
        if (isTrained) {
          row.querySelector('.stat-label').insertAdjacentHTML('beforeend', trainingMarkHtml(training));
        }
        row.querySelector('.stat-value').textContent = label === 'Счастье' ? '' : value;
        row.querySelector('i').style.width = Math.max(0, Math.min(100, isHp ? (hp / Math.max(1, hpMax) * 100) : (value / Math.max(1, max) * 100))) + '%';
        const evChip = row.querySelector('.stat-ev-chip');
        const plusButton = row.querySelector('.stat-plus');
        if (item.hasEv) {
          evChip.textContent = 'EV ' + evValue + '/' + evMaxPerStat;
          evChip.title = 'Можно влить ещё: ' + evRemaining + ' EV';
          evChip.classList.toggle('is-full', evRemaining <= 0);
          const vitaminChip = document.createElement('span');
          vitaminChip.className = 'stat-vitamin-chip' + (statVitaminLeft <= 0 ? ' is-empty' : '');
          vitaminChip.textContent = '×' + statVitaminLeft;
          vitaminChip.title = 'Можно влить ещё витаминов в этот стат: ' + statVitaminLeft;
          plusButton.title = 'EV: ' + evValue + '/' + evMaxPerStat + '. Можно влить ещё: ' + evRemaining + ' EV. Витаминов: ×' + statVitaminLeft;
          plusButton.setAttribute('aria-label', plusButton.title);
          plusButton.before(vitaminChip);
          plusButton.addEventListener('click', event => {
            event.stopPropagation();
            showEvPopup(row.querySelector('.stat-plus'), {
              label,
              ev: evValue,
              evMax: evMaxPerStat,
              remainingEv: evRemaining,
              vitamins: statVitaminLeft,
              totalEv: evTotal,
              totalMax: evMaxTotal,
              totalRemaining: evRemainingTotal,
            });
          });
        } else {
          evChip.textContent = '';
          evChip.style.visibility = 'hidden';
          plusButton.disabled = true;
          plusButton.textContent = '';
        }
        box.appendChild(row);
      });
    }

    function showLearnPopup(anchor, poke, slot, current) {
      closePopups();
      const currentId = Number(current && current.id || 0);
      const moves = (poke.learnableMoves || []).filter(move => Number(move.id) !== currentId);
      activeLearn = { pokeId: Number(poke.id), slot, moves, currentId };
      const pop = document.createElement('div');
      pop.className = 'learn-pop';
      pop.innerHTML = '<h3>Заменить атаку</h3><input class="learn-search" type="search" placeholder="Поиск по атаке, типу или ID"><div class="learn-results"></div>';
      document.body.appendChild(pop);
      const input = pop.querySelector('.learn-search');
      const results = pop.querySelector('.learn-results');
      const render = () => renderLearnResults(results, moves, input.value);
      input.addEventListener('input', render);
      render();
      placePopup(pop, anchor, 8);
      window.setTimeout(() => input.focus(), 0);
    }

    function normalizeSearch(value) {
      return String(value || '').toLowerCase().replace(/[^a-z0-9а-яё]+/giu, '');
    }

    function renderLearnResults(target, moves, query) {
      const q = normalizeSearch(query);
      const filtered = moves.filter(move => {
        if (!q) return true;
        return normalizeSearch(move.name).includes(q)
          || normalizeSearch(move.type).includes(q)
          || String(Number(move.id || 0)).includes(q)
          || String(Number(move.level || 0)).includes(q);
      }).slice(0, 36);

      target.innerHTML = '';
      if (!filtered.length) {
        const empty = document.createElement('div');
        empty.className = 'learn-empty';
        empty.textContent = moves.length ? 'Ничего не найдено.' : 'Нет доступных атак.';
        target.appendChild(empty);
        return;
      }
      filtered.forEach(move => target.appendChild(renderLearnRow(move)));
    }

    function renderLearnRow(move) {
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'learn-row';
      row.classList.add(typeClass(move.type || 'Normal'));
      row.title = typeLabel(move.type || 'Normal') + (Number(move.pp || 0) > 0 ? ', ' + Number(move.pp || 0) + ' PP' : '');
      row.innerHTML = '<span class="learn-type-icon"><img alt=""></span><strong></strong><small></small>';
      const icon = row.querySelector('.learn-type-icon');
      icon.classList.add(typeClass(move.type || 'Normal'));
      icon.title = typeLabel(move.type || 'Normal');
      row.querySelector('img').src = typeIconSrc(move.type || 'Normal');
      row.querySelector('img').alt = typeLabel(move.type || 'Normal');
      row.querySelector('strong').textContent = move.name || 'Атака';
      row.querySelector('small').textContent = 'Lv.' + Number(move.level || 0) + (Number(move.pp || 0) > 0 ? ' • ' + Number(move.pp || 0) + ' PP' : '');
      row.addEventListener('click', event => {
        event.stopPropagation();
        if (activeLearn) setMove(activeLearn.pokeId, activeLearn.slot, Number(move.id));
      });
      return row;
    }

    function showEvPopup(anchor, info) {
      closePopups();
      const data = info || {};
      const pop = document.createElement('div');
      pop.className = 'ev-pop';
      pop.innerHTML = '<strong></strong><div class="ev-pop-line"></div><div class="ev-pop-line"></div><div class="ev-pop-line"></div><button type="button">Понятно</button>';
      pop.querySelector('strong').textContent = data.label || 'EV';
      const lines = pop.querySelectorAll('.ev-pop-line');
      lines[0].textContent = 'EV: ' + Number(data.ev || 0) + '/' + Number(data.evMax || 252);
      lines[1].textContent = 'Можно влить ещё: ' + Number(data.remainingEv || 0) + ' EV';
      lines[2].textContent = 'Осталось витаминов: ×' + Number(data.vitamins || 0) + ' · общий лимит ' + Number(data.totalEv || 0) + '/' + Number(data.totalMax || 510);
      pop.querySelector('button').addEventListener('click', () => {
        closePopups();
      });
      document.body.appendChild(pop);
      placePopup(pop, anchor, 10);
    }

    function placePopup(pop, anchor, offset) {
      const rect = anchor.getBoundingClientRect();
      const width = pop.offsetWidth;
      const height = pop.offsetHeight;
      let left = rect.left + rect.width / 2 - width / 2;
      let top = rect.bottom + offset;
      left = Math.max(8, Math.min(left, window.innerWidth - width - 8));
      if (top + height > window.innerHeight - 8) {
        const above = rect.top - height - offset;
        top = above >= 8 ? above : Math.max(44, (window.innerHeight - height) / 2);
      }
      pop.style.left = left + 'px';
      pop.style.top = top + 'px';
    }

    function closePopups() {
      document.querySelectorAll('.learn-pop,.ev-pop,.held-confirm-pop').forEach(node => node.remove());
      activeLearn = null;
    }

    async function useTrainingItem(pokemonId, action) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      body.set('action', action);
      try {
        const response = await fetch('/api/pokemon/training', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        await load(true);
      } catch (error) {
        setStatus('Тренировка не выполнена.', true);
      }
    }

    async function setMove(pokemonId, slot, moveId) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      body.set('slot', slot);
      body.set('move_id', String(moveId));
      try {
        const response = await fetch('/api/pokemon/move', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        if (payload && payload.ok) {
          updateLocalMove(pokemonId, slot, moveId);
          renderAll();
        }
      } catch (error) {
        setStatus('Атака не обновлена.', true);
      }
    }

    async function nurseryAction(pokemonId, action) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      body.set('action', action);
      try {
        const response = await fetch('/api/pokemon/nursery', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        if (payload && Array.isArray(payload.pokemon)) {
          team = payload.pokemon;
          selectedId = 0;
          detailOpen = false;
          renderAll();
        } else {
          await load(false);
        }
      } catch (error) {
        setStatus('Питомник не обновлен.', true);
      }
    }

    function resetLocalState() {
      closePopups();
      selectedId = 0;
      detailOpen = false;
      activeLearn = null;
      renderAll();
    }

    async function reopenPanel() {
      closePopups();
      selectedId = 0;
      detailOpen = false;
      activeLearn = null;
      await load(false);
    }

    function updateLocalMove(pokemonId, slot, moveId) {
      const poke = team.find(item => Number(item.id) === Number(pokemonId));
      if (!poke) return;
      if (!poke.moves) poke.moves = {};
      const move = (poke.learnableMoves || []).find(item => Number(item.id) === Number(moveId));
      poke.moves[slot] = move
        ? { id: Number(move.id), name: move.name, type: move.type || 'Normal', pp: Number(move.pp || 0), ppMax: Number(move.pp || 0) }
        : { id: 0, name: 'Нет атаки' };
    }

    function selectedPokemon() {
      const active = activePokemon();
      return active.find(poke => Number(poke.id) === Number(selectedId)) || active[0] || null;
    }

    function displayName(poke) {
      return String(poke.name || ('Pokemon #' + Number(poke.baseNum || 0))).replace(/^#?\d+\s*/, '');
    }

    function heldItem(poke) {
      return poke && poke.heldItem && Number(poke.heldItem.id || 0) > 0 ? poke.heldItem : null;
    }

    function itemIconSrc(id) {
      const itemId = Number(id || 0);
      return '/public/img/items/' + itemId + '.png';
    }

    function setHeldIcon(img, itemId) {
      if (!img) return;
      const id = Number(itemId || 0);
      img.onerror = () => {
        img.onerror = () => { img.src = '/img/blank.gif'; };
        img.src = '/img/items/' + id + '.png';
      };
      img.src = itemIconSrc(id);
    }

    function renderHeldBadge(box, poke) {
      if (!box) return;
      const item = heldItem(poke);
      if (!item) {
        box.hidden = true;
        return;
      }
      box.hidden = false;
      box.title = item.name || ('Item #' + Number(item.id || 0));
      setHeldIcon(box.querySelector('img'), item.id);
    }

    function renderHeldDetail(box, poke) {
      if (!box) return;
      const item = heldItem(poke);
      if (!item) {
        box.hidden = true;
        box.onclick = null;
        box.onkeydown = null;
        box.removeAttribute('role');
        box.removeAttribute('tabindex');
        return;
      }
      box.hidden = false;
      setHeldIcon(box.querySelector('img'), item.id);
      box.querySelector('span').textContent = 'Держит: ' + (item.name || ('Item #' + Number(item.id || 0)));
      box.title = 'Нажми, чтобы снять предмет. ' + (item.title || item.name || '');
      box.setAttribute('role', 'button');
      box.tabIndex = 0;
      box.onclick = event => {
        event.stopPropagation();
        showHeldUnequipConfirm(box, poke, item);
      };
      box.onkeydown = event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          event.stopPropagation();
          showHeldUnequipConfirm(box, poke, item);
        }
      };
    }

    function showHeldUnequipConfirm(anchor, poke, item) {
      closePopups();
      const pop = document.createElement('div');
      pop.className = 'held-confirm-pop';
      pop.innerHTML = [
        '<strong>Снять предмет?</strong>',
        '<p></p>',
        '<div class="held-confirm-actions">',
          '<button type="button" class="confirm" data-held-confirm>Да</button>',
          '<button type="button" data-held-cancel>Нет</button>',
        '</div>'
      ].join('');
      pop.querySelector('p').textContent = 'Снять "' + (item.name || ('Item #' + Number(item.id || 0))) + '" с ' + displayName(poke) + ' и вернуть в инвентарь?';
      pop.querySelector('[data-held-confirm]').addEventListener('click', event => {
        event.stopPropagation();
        unequipHeldItem(Number(poke.id || 0));
      });
      pop.querySelector('[data-held-cancel]').addEventListener('click', event => {
        event.stopPropagation();
        closePopups();
      });
      document.body.appendChild(pop);
      placePopup(pop, anchor, 8);
    }

    async function unequipHeldItem(pokemonId) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      try {
        setStatus('Снимаем предмет...');
        const response = await fetch('/api/inventory/unequip', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        closePopups();
        if (payload && payload.ok) {
          await load(true);
        }
      } catch (error) {
        setStatus('Предмет не снят.', true);
      }
    }

    function hpPercent(poke) {
      return Math.max(0, Math.min(100, Number(poke.hp || 0) / Math.max(1, Number(poke.hpMax || 1)) * 100));
    }

    function pad3(value) {
      return String(Math.max(0, Number(value || 0))).padStart(3, '0');
    }

    function displayDexNumber(poke) {
      return Number(poke && (poke.dexNumber || poke.displayBaseNum || poke.baseId || poke.baseNum) || 0);
    }

    function isShiny(poke) {
      return String(poke.name || '').toLowerCase().includes('shiny');
    }

    function typeKey(type) {
      return String(type || 'Normal').toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function typeClass(type) {
      return 'type-' + typeKey(type);
    }

    function typeIconSrc(type) {
      const map = {
        bug: 1,
        dark: 2,
        dragon: 3,
        electric: 4,
        fighting: 5,
        fire: 6,
        flying: 7,
        ghost: 8,
        grass: 9,
        ground: 10,
        ice: 11,
        normal: 12,
        poison: 13,
        psychic: 14,
        steel: 15,
        rock: 16,
        water: 17,
        pokesno: 12,
        item: 12,
        topokes: 12
      };
      const names = {
        1: 'bug',
        2: 'dark',
        3: 'dragon',
        4: 'electric',
        5: 'fighting',
        6: 'fire',
        7: 'flying',
        8: 'ghost',
        9: 'grass',
        10: 'ground',
        11: 'ice',
        12: 'normal',
        13: 'poison',
        14: 'psychic',
        15: 'steel',
        16: 'rock',
        17: 'water'
      };
      return '/public/img/types/' + (names[map[typeKey(type)] || 12] || 'normal') + '.png';
    }

    function escapeHtml(value) {
      return String(value ?? '').replace(/[&<>'"]/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
      }[c]));
    }

    function typeLabel(type) {
      return String(type || 'Normal');
    }

    function ppText(move) {
      const pp = Number(move.pp || 0);
      const ppMax = Number(move.ppMax || 0);
      if (ppMax > 0) return pp + '/' + ppMax;
      return Number(move.pp || 0) > 0 ? String(pp) : 'PP';
    }

    function openDexPokemon(poke) {
      const dexId = Number(poke.baseNum || 0);
      if (dexId <= 0) return;
      if (window.parent && window.parent !== window && window.parent.PokemonDex) {
        window.parent.PokemonDex.openPokemon(dexId);
        const close = window.parent.document.getElementById('pokemonCloseBtn');
        if (close) close.click();
        return;
      }
      window.location.href = '/game/pokedex?id=' + encodeURIComponent(String(dexId));
    }

    function setSprite(img, poke) {
      const num = Number(poke.baseNum || 0);
      const padded = pad3(num);
      const urls = [];
      const assetSlug = spriteSlug(displayName(poke));
      if (isShiny(poke)) {
        urls.push('/Pok/shine/' + num + '.png');
        urls.push('/Pok/shiny/' + padded + '.gif');
        urls.push('/Pok/shiny/' + num + '.gif');
      }
      urls.push('/Pok/normal/' + num + '.png');
      urls.push('/Pok/' + num + '.jpg');
      if (assetSlug) {
        urls.push('/public/img/pokemon/art/' + assetSlug + '.png');
        urls.push('/public/img/pokemon/small/' + assetSlug + '.png');
      }
      urls.push('/Pok/pok/' + padded + '.gif');
      urls.push('/Pok/anim/' + padded + '.gif');
      let index = 0;
      img.onerror = () => {
        index += 1;
        img.src = urls[index] || '/img/blank.gif';
      };
      img.src = urls[index] || '/img/blank.gif';
    }

    function spriteSlug(name) {
      return String(name || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function bindDexOpen(node, poke) {
      if (!node) return;
      node.tabIndex = 0;
      node.setAttribute('role', 'button');
      node.title = 'Открыть в Покедексе';
      node.addEventListener('click', () => openDexPokemon(poke));
      node.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openDexPokemon(poke);
        }
      });
    }

    window.PokemonTeamPanel = {
      reset: resetLocalState,
      open: reopenPanel,
      refresh: () => load(false),
    };

    load(false);
  </script>
</body>
</html>
