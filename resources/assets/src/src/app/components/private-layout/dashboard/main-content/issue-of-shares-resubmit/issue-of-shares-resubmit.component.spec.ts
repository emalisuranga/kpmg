import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IssueOfSharesResubmitComponent } from './issue-of-shares-resubmit.component';

describe('IssueOfSharesResubmitComponent', () => {
  let component: IssueOfSharesResubmitComponent;
  let fixture: ComponentFixture<IssueOfSharesResubmitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ IssueOfSharesResubmitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IssueOfSharesResubmitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
