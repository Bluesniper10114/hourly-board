import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BillboardAddCommentComponent } from './billboard-add-comment.component';

describe('BillboardAddCommentComponent', () => {
  let component: BillboardAddCommentComponent;
  let fixture: ComponentFixture<BillboardAddCommentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BillboardAddCommentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BillboardAddCommentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
