import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BalanceSheetdateResubmitComponent } from './balance-sheetdate-resubmit.component';

describe('BalanceSheetdateResubmitComponent', () => {
  let component: BalanceSheetdateResubmitComponent;
  let fixture: ComponentFixture<BalanceSheetdateResubmitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BalanceSheetdateResubmitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BalanceSheetdateResubmitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
