import { Component, OnInit, Inject, ChangeDetectorRef } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA, MatSnackBar } from '@angular/material';
import {
  FormBuilder,
  FormGroup,
  Validators,
  FormArray,
  FormControl
} from '@angular/forms';
import {
  DownTimeModel,
  DownTimeSaveDTO,
  DownTimeRowModel,
  DownTimeReason
} from '../../models/downTime.model';
import { DrpDownListModel } from '@hourly-board-workspace/shared/fuse';

@Component({
  selector: 'hb-billboard-add-down-time',
  templateUrl: './billboard-add-down-time.component.html',
  styleUrls: ['./billboard-add-down-time.component.scss']
})
export class BillboardAddDownTimeComponent implements OnInit {
  //  fill the reasons dictionary
  reasons: DrpDownListModel[];
  //  input from api
  downTime: DownTimeModel;
  //  reactive form model
  downTimeForm: FormGroup;
  //  minutes select box  from 1 > 60 mins
  times: number[];
  //  flag to determine that all downtime has reasons
  validDuration: boolean;
  // enable / disable controls depending on firstOPen value
  ctrlsDisabled: boolean;

  //  returning the downtime reasons form array
  get downtimeReasonsArray(): FormArray {
    return <FormArray>this.downTimeForm.get('downtimeReasons');
  }

  constructor(
    public dialogRef: MatDialogRef<BillboardAddDownTimeComponent>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private formBuilder: FormBuilder,
    private cdr: ChangeDetectorRef,
    private snackBar: MatSnackBar
  ) {
    // populate times dropdown
    this.times = [];
    for (let m = 1; m < 60; m++) {
      this.times.push(m);
    }
  }

  openSnackBar(message: string, action: string, duration: number) {
    this.snackBar.open(message, action, {
      duration: duration,
      panelClass: 'warn'
    });
  }

  close(): void {
    // debugger;
    this.checkValidity();
    console.log(this.downTimeForm.getRawValue());
    if (this.validDuration && this.downTimeForm.valid) {
      const downTimeSaveDto: DownTimeSaveDTO = {
        hourlyId: this.downTime.hourlyId,
        timeStamp: this.downTime.timeStamp,
        downtimeReasons: this.downTimeForm.getRawValue().downtimeReasons
      };
      this.dialogRef.close(downTimeSaveDto);
    } else {
      this.openSnackBar('Check your inputs  ', 'Error', 2000);
    }
  }

  ngOnInit() {
    // debugger;
    // getting data from service
    this.reasons = this.data.reasons;
    this.downTime = this.data.downTime;
    // read only view if firstOpen = false ;
    debugger;
    this.ctrlsDisabled = this.data.disabled;
    // build the form
    this.downTimeForm = this.formBuilder.group({
      downtimeReasons: this.formBuilder.array(this.buildDownTime())
    });

    if (this.downTime.reasons.length) {
      this.updateDuration();
    }
  }

  /**
   * @description Create DownTime form model
   */

  // create form model
  buildDownTime(): FormGroup[] {
    const arr = [];
    this.downTime.intervals.forEach(item => {
      item.reasons = item.reasons ? item.reasons : [];
      this.downTime.reasons.forEach(reason => {
        if (reason.downtimeId === item.id) {
          item.reasons.push(reason);
        }
      });
      // debugger;
      arr.push(this.createDowntimeReasons(item));
    });
    return arr;
  }

  // create the form model containing downtime intervals
  createDowntimeReasons(item: DownTimeRowModel): FormGroup {
    return this.formBuilder.group({
      downtimeId: [{ value: item.id, disabled: true }],
      timeInterval: [{ value: item.timeInterval, disabled: true }],
      machine: [{ value: item.machine, disabled: true }],
      validatedDuration: [{ value: 0, disabled: true }],
      totalDuration: [{ value: item.totalDuration, disabled: true }],
      reasons: this.formBuilder.array(this.buildReasonsForm(item.reasons))
    });
  }

  // build reasons for if it hass prefilled reasons or create a first row (reason)
  buildReasonsForm(reasons: DownTimeReason[]): FormGroup[] {
    const arr = [];
    // debugger;
    if (reasons.length) {
      reasons.forEach(reason => {
        arr.push(this.createReasonForm(reason));
      });
    } else {
      arr.push(this.createReasonForm());
    }
    return arr;
  }

  // create the form model containing reasons inside  intervals
  createReasonForm(reason?: DownTimeReason): FormGroup {
    if (!reason) {
      reason = {
        id: 0,
        duration: null,
        timeStamp: this.downTime.timeStamp,
        reason: null
      };
    }
    return this.formBuilder.group({
      id: [{ value: reason.id, disabled: this.ctrlsDisabled }],
      duration: [
        { value: reason.duration, disabled: this.ctrlsDisabled },
        Validators.required
      ],
      timeStamp: [this.downTime.timeStamp],
      reason: [
        { value: reason.id, disabled: this.ctrlsDisabled },
        Validators.required
      ]
    });
  }

  // loops through the form controls and check the reasons prefilled
  updateDuration(): void {
    // debugger;
    this.downtimeReasonsArray.controls.forEach(ctrl => {
      // debugger;
      const index = this.downtimeReasonsArray.controls.indexOf(ctrl);
      this.onDurationChange(index, null);
    });
  }

  checkValidity(): void {
    // debugger;
    this.downtimeReasonsArray.controls.forEach(ctrl =>
      ctrl.updateValueAndValidity()
    );
  }

  addItem(index: number, reasonIndex: number): void {
    const dtReasons = (<any>(
      this.downtimeReasonsArray.controls[index].get('reasons')
    )).controls as any;
    dtReasons.push(this.createReasonForm());

    if (this.downTime.reasons.length) {
      // debugger;
      dtReasons.forEach(ctrl => ctrl.updateValueAndValidity());
    }
    this.cdr.detectChanges();
  }

  removeItem(intervalIndex: number, reasonIndex: number): void {
    // debugger;
    const dtReasons = (<any>(
      this.downtimeReasonsArray.controls[intervalIndex].get('reasons')
    )) as FormArray;

    dtReasons.removeAt(reasonIndex);
    this.onDurationChange(intervalIndex, reasonIndex);
    dtReasons.updateValueAndValidity();
    this.cdr.detectChanges();
  }

  //  updating validate duration and the validation flag
  onDurationChange(intervalIndex: number, reasonIndex: number): void {
    // debugger;

    const validatedDuration = this.downtimeReasonsArray.controls[
      intervalIndex
    ].get('validatedDuration');
    const totalDuration = this.downtimeReasonsArray.controls[intervalIndex].get(
      'totalDuration'
    );

    let selectedDurations = 0;
    (<FormArray>(
      this.downtimeReasonsArray.controls[intervalIndex].get('reasons')
    )).controls.forEach(ctrl => {
      // debugger;
      selectedDurations += ctrl.get('duration').value;
    });

    if (selectedDurations > totalDuration.value) {
      this.openSnackBar('Selection is invalid ', 'Error', 2000);
      if (
        (<FormArray>(
          this.downtimeReasonsArray.controls[intervalIndex].get('reasons')
        )).controls[reasonIndex]
      ) {
        (<FormArray>(
          this.downtimeReasonsArray.controls[intervalIndex].get('reasons')
        )).controls[reasonIndex]
          .get('duration')
          .setValue(0);
      }
      validatedDuration.setValue(0);
      return;
    }

    validatedDuration.setValue(selectedDurations);

    if (validatedDuration.value === totalDuration.value) {
      this.validDuration = true;
    } else {
      this.validDuration = false;
    }

    const dtReasons = (<any>(
      this.downtimeReasonsArray.controls[intervalIndex].get('reasons')
    )) as FormArray;

    // debugger;
    // this is to reflect the changes done in components code into html code
    dtReasons.updateValueAndValidity();
    dtReasons.controls.forEach(ctrl => ctrl.updateValueAndValidity());
    this.cdr.detectChanges();
  }
}
