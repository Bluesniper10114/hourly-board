import { Component, OnInit, OnDestroy } from '@angular/core';
import { Observable, of, Subscription } from 'rxjs';
import { FormGroup } from '@angular/forms';
import { MatDialog, MatSnackBar } from '@angular/material';
import { map } from 'rxjs/operators';

import {
  FuseConfigService,
  FuseTranslationLoaderService,
  NotifyService,
  fuseAnimations,
  CommonHandlersService,
  FooterModel,
  DrpDownListModel
} from '@hourly-board-workspace/shared/fuse';

import { BillboardService } from '../../services/billboard.service';

import {
  BillboardModel,
  HoursModel,
  findTextInRegx,
  splitString
} from '../../models/billboard.model';

import { locale as english } from '../../i18n/en';
import { locale as romanian } from '../../i18n/ro';

import { BillboardAddCommentComponent } from '../billboard-add-comment/billboard-add-comment.component';
import { environment } from '@env/environment';
import { DownTimeModel, DownTimeSaveDTO } from '../../models/downTime.model';
import { BillboardAddDownTimeComponent } from '../billboard-add-down-time/billboard-add-down-time.component';
import { SPINNER_PLACEMENT, SPINNER_ANIMATIONS } from '@hardpool/ngx-spinner';

@Component({
  selector: 'hb-billboard-main',
  templateUrl: './billboard-main.component.html',
  styleUrls: ['./billboard-main.component.scss'],
  animations: fuseAnimations
})
export class BillboardMainComponent implements OnInit, OnDestroy {
  billboardData$: Observable<BillboardModel>;
  footerData$: Observable<FooterModel>;

  dialogRef: any;
  display: any;
  timerInterval: any;

  getCommentsSub: Subscription;
  getEscalationSub: Subscription;

  getDownTimesSub: Subscription;
  getReasonsSub: Subscription;
  onGetReasonsChanged: Subscription;
  onLoading$: Subscription;

  loading: boolean;
  billboardDataLoaded: boolean;

  loadingSpinnerConfig: any;
  comments: DrpDownListModel[];
  escalations: DrpDownListModel[];
  downTimeReasons: DrpDownListModel[];

  constructor(
    private _fuseConfigService: FuseConfigService,
    private billboardService: BillboardService,
    private commonHandler:CommonHandlersService,
    private _fuseTranslationLoaderService: FuseTranslationLoaderService,
    public dialog: MatDialog,
    private notifyService: NotifyService
  ) {
    // configuring spinner when bilboard data is loading
    this.loadingSpinnerConfig = {
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

    // Load the translations
    this._fuseTranslationLoaderService.loadTranslations(english, romanian);
  }

  onOpenDownTime($event: HoursModel): void {
    this.onGetReasonsChanged = this.billboardService.onGetDownTimeReasons.subscribe(
      data => {
        if (data.length) {
          this.downTimeReasons = data;
        }
      }
    );

    this.getDownTimesSub = this.billboardService
      .getHourlyDownTime($event.id)
      .subscribe(downTime => {
        this.openDownTimeModal(downTime, $event);
      });
  }

  openDownTimeModal(downTime: DownTimeModel, hour?: HoursModel): void {
    this.dialogRef = this.dialog.open(BillboardAddDownTimeComponent, {
      width: '1030px',
      disableClose: true,
      data: {
        downTime,
        reasons: this.downTimeReasons,
        disabled: hour.firstOpen ? false : true
      }
    });

    this.dialogRef.afterClosed().subscribe((downTimeForm: DownTimeSaveDTO) => {
      // debugger;
      if (!downTimeForm) {
        return;
      }

      if (downTimeForm) {
        this.billboardService.saveDownTime(downTimeForm).subscribe(response => {
          debugger;
          if (response) {
            if (response.errorMessage) {
              this.notifyService.showError('DownTime', response.errorMessage);
            } else {
              this.notifyService.showSuccess(
                'DownTime',
                '  DownTime Explained, Successfully !'
              );
            }
          }
        });
      }
    });
  }

  onSignOffShift($event: {
    shiftLogSignOffId: number;
    operatorBarcode: string;
  }): void {
    this.billboardService.signOffShift($event).subscribe(response => {
      if (response.errorMessage) {
        this.notifyService.showError('Sign Off Shift', response.errorMessage);
      } else {
        this.notifyService.showSuccess(
          'Sign Off Shift',
          '  Signed Off Shift, Successfully !'
        );
        setTimeout(() => {
          this.initData();
          // window.location.reload();
        }, 100);
      }
    });
  }

  onSignOffHour($event: { hourlyId: number; operatorBarcode: string }): void {
    this.billboardService.signOffHour($event).subscribe(response => {
      if (response.errorMessage) {
        this.notifyService.showError('Sign Off ', response.errorMessage);
      } else {
        this.notifyService.showSuccess(
          'Sign Off ',
          '  Signed Off, Successfully !'
        );
        setTimeout(() => {
          this.initData();
          // window.location.reload();
        }, 500);
      }
    });
  }

  onAddEscalation($event: HoursModel): void {
    this.openCommentsDialog($event, null, this.escalations);
  }

  onAddComment($event: HoursModel): void {
    this.openCommentsDialog($event, this.comments, null);
  }

  onEditComment($event: HoursModel): void {
    // debugger;
    const founds = [];
    $event.commentsArray.forEach(comment => {
      founds.push(
        this.comments.filter(str => {
          return findTextInRegx(str.text, comment);
        })[0].id
      );
    });
    this.openCommentsDialog($event, this.comments, null, founds);
  }

  onEditEscalations($event: HoursModel): void {
    // debugger;
    const founds = [];
    $event.escalationsArray.forEach(esc => {
      founds.push(
        this.escalations.filter(str => {
          return findTextInRegx(str.text, esc);
        })[0].id
      );
    });
    this.openCommentsDialog($event, null, this.escalations, founds);
  }

  openCommentsDialog(
    hour: HoursModel,
    comments?: DrpDownListModel[],
    escalations?: DrpDownListModel[],
    selectedIds?: number[]
  ): void {
    this.dialogRef = this.dialog.open(BillboardAddCommentComponent, {
      width: '550px',
      data: {
        list: comments ? comments : escalations,
        hourId: hour.id,
        type: comments ? 'Add Comments' : 'Add Escalations',
        selectedIds: selectedIds ? selectedIds : []
      }
    });

    this.dialogRef.afterClosed().subscribe((commentsForm: FormGroup) => {
      // debugger;
      if (!commentsForm) {
        return;
      }
      if (commentsForm.valid) {
        if (comments) {
          this.billboardService
            .postComments(commentsForm.getRawValue())
            .subscribe(response => {
              debugger;
              if (response.errorMessage) {
                this.notifyService.showError(
                  'Add Comments',
                  response.errorMessage
                );
              } else {
                hour.commentsArray = splitString(response.comment);
                hour.comments = response.comment;
                this.notifyService.showSuccess(
                  'Add Comments',
                  'comments Added Successfully !'
                );
              }
            });
        } else {
          this.billboardService
            .postEscalations(commentsForm.getRawValue())
            .subscribe(response => {
              debugger;
              if (response.errorMessage) {
                this.notifyService.showError(
                  'Add Escalations',
                  response.errorMessage
                );
              } else {
                hour.escalationsArray = splitString(response.escalations);
                hour.escalations = response.escalations;
                this.notifyService.showSuccess(
                  'Add Escalations',
                  'Escalations Added Successfully !'
                );
              }
            });
        }
      }
    });
  }

  initData(): void {
    if (this.billboardDataLoaded) {
      this.billboardDataLoaded = false;
    }

    // check if the timer is running or not
    // debugger;
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
    this.startTimer(60 * environment.reloadMins);

    this.getReasonsSub = this.billboardService.getDownTimeReasons();

    this.billboardData$ = this.billboardService.getBillboard().pipe(
      map(data => {
        // debugger;
        this.billboardDataLoaded = true;
        if (data) {
          return new BillboardModel(data);
        }
      })
    );

    this.getCommentsSub = this.billboardService
      .getComments()
      .subscribe(comments => {
        this.comments = comments;
      });

    this.getEscalationSub = this.billboardService
      .getEscalations()
      .subscribe(escalations => {
        this.escalations = escalations;
      });
  }

  ngOnInit() {
    this.initData();

    this.footerData$ = this.billboardService.getBoardFooter();

    this.onLoading$ = this.commonHandler.loading$.subscribe(loadingFlag => {
      this.loading = loadingFlag;
    });
  }

  ngOnDestroy(): void {
    this.getCommentsSub.unsubscribe();
    this.getEscalationSub.unsubscribe();
    if (this.getDownTimesSub) {
      this.getDownTimesSub.unsubscribe();
    }
    if (this.onGetReasonsChanged) {
      this.onGetReasonsChanged.unsubscribe();
    }
    if (this.getReasonsSub) {
      this.getReasonsSub.unsubscribe();
    }
    this.onLoading$.unsubscribe();
  }

  startTimer(duration) {
    let timer = duration,
      minutes,
      seconds;
    this.timerInterval = setInterval(() => {
      // debugger;
      minutes = parseInt(<any>(timer / 60), 10);
      seconds = parseInt(<any>(timer % 60), 10);

      minutes = minutes < 10 ? '0' + minutes : minutes;
      seconds = seconds < 10 ? '0' + seconds : seconds;

      this.display = minutes + ':' + seconds;

      if (--timer < 0) {
        // debugger;
        clearInterval(this.timerInterval);
        this.initData();
      }
    }, 1000);
  }
}
