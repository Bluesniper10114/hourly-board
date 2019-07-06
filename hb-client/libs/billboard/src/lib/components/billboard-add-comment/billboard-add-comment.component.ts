import { Component, OnInit, Inject } from '@angular/core';
import { FormGroup, FormBuilder } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';
import { DrpDownListModel } from '@hourly-board-workspace/shared/fuse';


@Component({
  selector: 'hb-billboard-add-comment',
  templateUrl: './billboard-add-comment.component.html',
  styleUrls: ['./billboard-add-comment.component.scss']
})
export class BillboardAddCommentComponent implements OnInit {
  type: string;
  list: DrpDownListModel;
  commentsForm: FormGroup;
  comment: {
    hourlyId: number;
    comments: number[];
  };
  escalation: {
    hourlyId: number;
    escalations: number[];
  };
  constructor(
    public dialogRef: MatDialogRef<BillboardAddCommentComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any,
    private formBuilder: FormBuilder
  ) {}

  createCommentsForm(): FormGroup {
    return this.formBuilder.group({
      hourlyId: [this.comment.hourlyId],
      comments: [this.comment.comments]
    });
  }

  createEscalationsForm(): FormGroup {
    return this.formBuilder.group({
      hourlyId: [this.escalation.hourlyId],
      escalations: [this.escalation.escalations]
    });
  }

  ngOnInit() {
    this.type = this.data.type;
    this.list = this.data.list;
    if (this.type === 'Add Comments') {
      // debugger;
      // this.dialogTitle = this.data.selectedIds.length
      //   ? 'Edit Comments'
      //   : this.type;
      this.comment = {
        hourlyId: this.data.hourId,
        comments: this.data.selectedIds
      };
      this.commentsForm = this.createCommentsForm();
    } else {
      // debugger;
      // this.dialogTitle = this.data.selectedIds.length
      //   ? 'Edit Escalations'
      //   : this.type;
      this.escalation = {
        hourlyId: this.data.hourId,
        escalations: this.data.selectedIds
      };
      this.commentsForm = this.createEscalationsForm();
    }
  }
}
