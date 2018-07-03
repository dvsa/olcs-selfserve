// Test
pipeline {
  agent {
     node {
        label 'slave'
        // The project will hang if the label is not set correctly. It will not time out!!
     }
  }
  environment {
	gitRepo='git@repo.shd.ci.nonprod.dvsa.aws:Permits/olcs-selfserve.git'
	slackChan='#platform_test'
	slackInit='#D4DADF'
	slackAbort='#FF9FA1'
	slackFail='#FF9FA1'
	slackSuccess='#8DFFC3'
	slackCompleted='#4A821C'
	}
 options {
        gitlabBuilds(builds: ['Initialize project', 'Fetch composer.phar', 'Composer update', 'Lint PHP', 'Unit Tests', 'Build Artefact'])
}
 stages {
  stage ('Initialide project') {
    steps {
	gitlabCommitStatus (name: 'Initialize project') {
		slackSend (channel: slackChan, color: slackInit, message: "STARTED:  '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n User:         '${env.gitlabMergedByUser}'\n Logs:\t     ${env.BUILD_URL}console")
        	checkout changelog: false, poll: false, scm: [$class: 'GitSCM', branches: [[name: '*/develop']], doGenerateSubmoduleConfigurations: false, extensions: [], submoduleCfg: [], userRemoteConfigs: [[credentialsId: 'kex', url: gitRepo]]]
                }
	      }
            }
 stage('Fetch composer.phar') {
   steps {
	gitlabCommitStatus (name: 'Fetch composer.phar') {
	        withAWS(region:'eu-west-1') {
        	s3Download(file:'composer.phar', bucket: 'rpm-repo001', path: 'composer.phar', force: true)
		slackSend (channel: slackChan, color: slackSuccess, message: "UPDATE:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n PASSED:\t ${env.STAGE_NAME}")
	}
      }
    }
  }
 stage('Composer update') {
   steps {
	gitlabCommitStatus (name: 'Composer update') {
        sh 'php ./composer.phar update --optimize-autoloader --no-interaction'
        slackSend (channel: slackChan, color: slackSuccess, message: "UPDATE:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n PASSED:\t ${env.STAGE_NAME}")
        }
     }
  }
 stage('Run tests') {
   parallel {
 	stage('Lint PHP') {
	   steps {
		gitlabCommitStatus (name: 'Lint PHP') {
       		sh 'find ./* -name "*.php" -not -path "*/vendor/*" -exec php -l {} ";"'
		slackSend (channel: slackChan, color: slackSuccess, message: "UPDATE:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n PASSED:\t ${env.STAGE_NAME}")
	 		}
		}
		}
 	
	stage('Unit tests') {
           steps {
    		gitlabCommitStatus (name: 'Unit tests') {
        	sh 'vendor/bin/phpunit -ctest/phpunit.xml'
        	slackSend (channel: slackChan, color: slackSuccess, message: "UPDATE:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n PASSED:\t ${env.STAGE_NAME}")
        }
    }
 }
       }
     }
 stage('Build artefact') {
   steps {
	gitlabCommitStatus (name: 'Build Artefact') {
	sh 'tar cvzf olcs-backend-ecmt-pipelinetest.tar.gz --exclude=config/autoload/local.php --exclude=config/autoload/local.php.dist composer.lock init_autoloader.php config module public data/autoload data/cache vendor'
        slackSend (channel: slackChan, color: slackSuccess, message: "UPDATE:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n PASSED:\t ${env.STAGE_NAME}")
	}
	}
    }
/* stage('Deploying artefact to s3') {
   steps {
	withAWS(region:'eu-west-1', credentials: 'Jenkins-AWS') {
	s3Upload(file:'olcs-backend-ecmt-pipelinetest.tar.gz', bucket: 'devapp-olcs-pri-olcs-deploy-s3', path: 'olcs-backend-ecmt-pipelinetest.tar.gz')
	slackSend (channel: slackChan, color: slackSuccess, message: "UPDATE:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n PASSED:\t ${env.STAGE_NAME}")
        }
    }
}
*/
}
post {
   success {
        slackSend (channel: slackChan, color: slackCompleted, message: "SUCCESSFUL:\t ${env.JOB_NAME} [${env.BUILD_NUMBER}]\n User:         ${env.gitlabMergedByUser}\n  Logs:\t     ${env.BUILD_URL}console")
	}
   failure {
        slackSend (channel: slackChan, color: slackFail, message: "FAILED:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n User:         '${env.gitlabMergedByUser}'\n  Logs:\t     ${env.BUILD_URL}console")
	}
   aborted {
        slackSend (channel: slackChan, color: slackAbort, message: "ABORTED:\t Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'\n User:         '${env.gitlabMergedByUser}'\n  Logs:\t     ${env.BUILD_URL}console")
	}
}
}
