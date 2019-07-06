import { Component, OnInit, OnDestroy } from '@angular/core';
import { FooterModel,CommonHandlersService } from '@hourly-board-workspace/shared/fuse';
import {
  ISpinnerConfig,
  SPINNER_PLACEMENT,
  SPINNER_ANIMATIONS
} from '@hardpool/ngx-spinner';
import { FooterService } from './footer.service';
import { Observable, Subscription } from 'rxjs';

@Component({
  // tslint:disable-next-line:component-selector
  selector: 'footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.scss']
})
export class FooterComponent implements OnInit, OnDestroy {
  footerData$: Observable<FooterModel>;
  config: ISpinnerConfig;
  loading: boolean;

  loading$: Subscription;

  constructor(private footerService: FooterService,private commonHandler:CommonHandlersService) {
    this.config = {
      size: '3.5rem',
      color: '#6086FF',
      placement: SPINNER_PLACEMENT.block_ui,
      animation: SPINNER_ANIMATIONS.bars
    };
  }

  ngOnInit(): void {
    this.loading$ = this.commonHandler.loading$.subscribe(loading => {
      this.loading = loading;
    });
    this.footerData$ = this.footerService.getBoardFooter();
  }

  ngOnDestroy(): void {
    this.loading$.unsubscribe();
  }
}
