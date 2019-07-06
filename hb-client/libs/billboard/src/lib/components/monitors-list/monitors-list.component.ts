import { Component, OnInit } from '@angular/core';
import { BillboardService } from '../../services/billboard.service';
import { Observable } from 'rxjs';
import {
  FuseConfigService,
  FuseTranslationLoaderService,
  NotifyService,
  fuseAnimations
} from '@hourly-board-workspace/shared/fuse';
import { MatDialog } from '@angular/material';
import { MonitorModel } from '../../models/monitor.model';
import { SPINNER_PLACEMENT, SPINNER_ANIMATIONS } from '@hardpool/ngx-spinner';
import { Route, Router } from '@angular/router';

@Component({
  selector: 'hb-monitors-list',
  templateUrl: './monitors-list.component.html',
  styleUrls: ['./monitors-list.component.scss'],
  animations: fuseAnimations
})
export class MonitorsListComponent implements OnInit {
  // monitors data from API
  monitors$: Observable<MonitorModel[]>;
  // loading flag for monitors
  monitorLoaded: boolean;

  spinnerConifg: any;
  constructor(
    private _fuseConfigService: FuseConfigService,
    private billboardService: BillboardService,
    private _fuseTranslationLoaderService: FuseTranslationLoaderService,
    public dialog: MatDialog,
    private notifyService: NotifyService,
    private router: Router
  ) {
    // configuring spinner when bilboard data is loading
    this.spinnerConifg = {
      size: '2.5rem',
      color: '#6086FF',
      placement: SPINNER_PLACEMENT.block_ui,
      animation: SPINNER_ANIMATIONS.rotating_dots
    };

    // Configure the layout
    this._fuseConfigService.config = {
      layout: {
        navbar: {
          hidden: true
        },
        toolbar: {
          hidden: true
        },
        footer: {
          hidden: true
        },
        sidepanel: {
          hidden: true
        }
      }
    };

    this.monitorLoaded = false;
  }

  // open monitor page
  onOpenMonitorByID($event: number): void {
    this.router.navigate(['/billboard/monitor', $event]);
  }

  ngOnInit() {
    // getting data from service
    this.monitors$ = this.billboardService.getMonitors().pipe(data => {
      // debugger;
      if (data) {
        this.monitorLoaded = true;
      }
      return data;
    });
  }
}
