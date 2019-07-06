import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BillboardFooterComponent } from './billboard-footer.component';

describe('BillboardFooterComponent', () => {
  let component: BillboardFooterComponent;
  let fixture: ComponentFixture<BillboardFooterComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BillboardFooterComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BillboardFooterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
