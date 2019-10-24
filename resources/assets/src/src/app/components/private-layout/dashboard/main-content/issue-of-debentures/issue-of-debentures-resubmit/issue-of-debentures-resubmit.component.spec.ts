import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IssueOfDebenturesResubmitComponent } from './issue-of-debentures-resubmit.component';

describe('IssueOfDebenturesResubmitComponent', () => {
  let component: IssueOfDebenturesResubmitComponent;
  let fixture: ComponentFixture<IssueOfDebenturesResubmitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ IssueOfDebenturesResubmitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IssueOfDebenturesResubmitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
