import { Component, OnInit, Input } from '@angular/core';

import {
  ISpinnerConfig,
  SPINNER_PLACEMENT,
  SPINNER_ANIMATIONS
} from '@hardpool/ngx-spinner';
import { FooterModel } from '@hourly-board-workspace/shared/fuse';

@Component({
  selector: 'hb-billboard-footer',
  templateUrl: './billboard-footer.component.html',
  styleUrls: ['./billboard-footer.component.scss']
})
export class BillboardFooterComponent implements OnInit {
  @Input() footerData: FooterModel;
  @Input() display: any;
  @Input() loading: boolean;
  config: ISpinnerConfig;

  constructor() {
    this.config = {
      size: '3.5rem',
      color: '#6086FF',
      placement: SPINNER_PLACEMENT.block_ui,
      animation: SPINNER_ANIMATIONS.bars
    };
  }

  ngOnInit() {}
}
