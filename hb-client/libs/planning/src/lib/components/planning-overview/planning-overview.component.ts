import { Component, OnInit, OnDestroy } from '@angular/core';
import { Observable, Subscription } from 'rxjs';
import { PlanningService } from '../../services/planning.service';
import { SPINNER_PLACEMENT, SPINNER_ANIMATIONS } from '@hardpool/ngx-spinner';
import {
  DrpDownListModel,
  CommonHandlersService
} from '@hourly-board-workspace/shared/fuse';
import { DataSetsModel } from '../../models/datasets.model';

@Component({
  selector: 'hb-admin-planning-overview',
  templateUrl: './planning-overview.component.html',
  styleUrls: ['./planning-overview.component.scss']
})
export class PlanningOverviewComponent implements OnInit, OnDestroy {
  // lines select boxes data
  lines$: Observable<DrpDownListModel[]>;
  dates$: Observable<string[]>;

  // dataSets Object
  dataSets$: Observable<DataSetsModel>;

  // configer the loading animations
  onLoading$: Subscription;
  loading: boolean;
  loadingSpinnerConfig: any;
  constructor(
    private planningService: PlanningService,
    private commonHandler: CommonHandlersService
  ) {
    // configuring spinner when bilboard data is loading
    this.loadingSpinnerConfig = {
      size: '2.5rem',
      color: '#6086FF',
      placement: SPINNER_PLACEMENT.block_ui,
      animation: SPINNER_ANIMATIONS.rotating_dots
    };
  }

  ngOnInit() {
    // calling Lines api in  planning  service
    this.lines$ = this.planningService.getPlanningLines();
    this.dates$ = this.planningService.getPlanningDates();

    // subscribing to the loading behavior subject in common handlers service
    this.onLoading$ = this.commonHandler.loading$.subscribe(loadingFlag => {
      this.loading = loadingFlag;
    });
  }

  // calling the service to get datasets
  onGetDataSets($event: {
    lines: number[];
    dates: string[];
    shifts: string[];
  }): void {
    this.dataSets$ = this.planningService.getPlanningDataSets(
      $event.lines,
      $event.dates,
      $event.shifts
    );
  }

  ngOnDestroy(): void {
    // cleaning the subscriptions from the component life-cycle
    this.onLoading$.unsubscribe();
  }
}
