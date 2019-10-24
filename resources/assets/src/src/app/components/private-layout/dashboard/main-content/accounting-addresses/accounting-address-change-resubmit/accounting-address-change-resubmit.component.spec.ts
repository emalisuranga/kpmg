import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccountingAddressChangeResubmitComponent } from './accounting-address-change-resubmit.component';

describe('AccountingAddressChangeResubmitComponent', () => {
  let component: AccountingAddressChangeResubmitComponent;
  let fixture: ComponentFixture<AccountingAddressChangeResubmitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccountingAddressChangeResubmitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccountingAddressChangeResubmitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
